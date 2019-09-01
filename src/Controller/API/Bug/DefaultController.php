<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\API\Bug;

use App\Entity\Bug;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use App\Repository\Security\ApiUserRepository;
use App\Repository\TrackerRepository;
use App\Request\Bug\CreateBugRequest;
use App\Request\Bug\UpdateBugRequest;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Default bug controller
 *
 * @Route(name="bug_")
 *
 * @SWG\Tag(name="Bug")
 */
class DefaultController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * DefaultController constructor.
     *
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/tracker/{id}/bug/", name="create", methods={"post"})
     *
     * @SWG\Response(
     *     response="201",
     *     description="Creates bug.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Tracker or developer not found."
     * )
     *
     * @SWG\Parameter(
     *     name="Create bug request",
     *     required=true,
     *     in="body",
     *     @Model(type=CreateBugRequest::class)
     * )
     *
     * @param int                               $id
     * @param \App\Request\Bug\CreateBugRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    public function createAction(int $id, CreateBugRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var Tracker $tracker */
        $tracker = $this->getTrackerRepository()->find($id);

        if (empty($tracker)) {
            throw new NotFoundHttpException('Tracker not found');
        }

        /** @var ApiUser $developer */
        $developer = $this->getUserRepository()->find($request->getResponsiblePerson());

        if (
            empty($developer) ||
            !$developer->isDeveloper() ||
            !$tracker->getProject()->getDevelopers()->contains($developer)
        ) {
            throw new NotFoundHttpException('Developer not found in this project');
        }

        /** @var ApiUser $author */
        $author = $this->getUser();

        $bug = $author->createBugFromRequest($request, $tracker, $developer);

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($bug, 'json', SerializationContext::create()->setGroups([
                'bug_details',
                'user_list',
                'tracker_list',
            ]))
        );
    }

    /**
     * @Route("/bug/{id}", name="show", methods={"get"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed info about the bug.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Bug not found.",
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $bug = $this->getBugRepository()->find($id);

        if (empty($bug)) {
            throw new NotFoundHttpException('Bug not found');
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($bug, 'json', SerializationContext::create()->setGroups([
                'bug_details',
                'user_list',
                'tracker_list',
            ]))
        );
    }

    /**
     * @Route("/bug/{id}/", name="update", methods={"put"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Updates the bug.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Bug or developer not found.",
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
     *
     * @SWG\Parameter(
     *     name="Update bug request",
     *     required=true,
     *     in="body",
     *     @Model(type=UpdateBugRequest::class)
     * )
     *
     * @param int                               $id
     * @param \App\Request\Bug\UpdateBugRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(int $id, UpdateBugRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var Bug $bug */
        $bug = $this->getBugRepository()->find($id);

        if (empty($bug)) {
            throw new NotFoundHttpException('The bug not found.');
        }

        /** @var ApiUser $developer */
        $developer = $this->getUserRepository()->find($request->getResponsiblePerson());
        $tracker   = $bug->getTracker();

        if (
            empty($developer) ||
            !$developer->isDeveloper() ||
            !$tracker->getProject()->getDevelopers()->contains($developer)
        ) {
            throw new NotFoundHttpException('Developer not found in this project');
        }

        $bug->updateFromRequest($request, $developer);

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($bug, 'json', SerializationContext::create()->setGroups([
                'bug_details',
                'user_list',
                'tracker_list',
            ]))
        );
    }

    /**
     * @Route("/bug/{id}/", name="delete", methods={"delete"})
     *
     * @SWG\Response(
     *     response="204",
     *     description="Removes the bug."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Bug not found."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $bug = $this->getBugRepository()->find($id);

        if (empty($bug)) {
            throw new NotFoundHttpException('The bug not found.');
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($bug);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return \App\Repository\BugRepository
     */
    private function getBugRepository()
    {
        return $this->getDoctrine()->getRepository(Bug::class);
    }

    /**
     * @return \App\Repository\TrackerRepository
     */
    private function getTrackerRepository(): TrackerRepository
    {
        return $this->getDoctrine()->getRepository(Tracker::class);
    }

    /**
     * @return \App\Repository\Security\ApiUserRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->getDoctrine()->getRepository(ApiUser::class);
    }
}
