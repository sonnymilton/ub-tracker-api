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
use App\Log\Bug\BugLogEntryAdapterFactory;
use App\Repository\TrackerRepository;
use App\Request\Bug\CreateBugRequest;
use App\Request\Bug\UpdateBugRequest;
use App\Serializer\AutoserializationTrait;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
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
    const LIST_SERIALIZATION_GROUPS    = ['bug_details', 'user_list', 'tracker_list'];
    const DETAILS_SERIALIZATION_GROUPS = ['bug_details', 'user_list', 'tracker_list', 'comment_list'];
    use AutoserializationTrait;

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
     *     @SWG\Schema(ref="#/definitions/Bug")
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

        $tracker   = $this->getTracker($id);
        $developer = $this->getDeveloper($request->getResponsiblePerson(), $tracker);

        /** @var ApiUser $author */
        $author = $this->getUser();

        $bug = $author->createBugFromRequest($request, $tracker, $developer);

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($bug));
    }

    /**
     * @Route("/bug/{id}", name="show", methods={"get"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed info about the bug.",
     *     @SWG\Schema(ref="#/definitions/Bug")
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
        $bug = $this->getBug($id);

        return JsonResponse::fromJsonString($this->autoserialize($bug));
    }

    /**
     * @Route("/bug/{id}/", name="update", methods={"put"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Updates the bug.",
     *     @SWG\Schema(ref="#/definitions/Bug")
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

        $bug       = $this->getBug($id);
        $tracker   = $bug->getTracker();
        $developer = $this->getDeveloper($request->getResponsiblePerson(), $tracker);

        $bug->updateFromRequest($request, $developer);

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($bug));
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

        $bug = $this->getBug($id);

        $em = $this->getDoctrine()->getManager();

        $em->remove($bug);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/bug/{id}/history/",  name="history", methods={"GET"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns bug's revision history."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Bug not found."
     * )
     *
     * @param int                                    $id
     * @param \App\Log\Bug\BugLogEntryAdapterFactory $logEntryAdapterFactory
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function historyAction(int $id, BugLogEntryAdapterFactory $logEntryAdapterFactory): JsonResponse
    {
        $bug        = $this->getBug($id);
        $logEntries = $this->getLogEntryRepository()->getLogEntries($bug);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize(
                $logEntryAdapterFactory->createAdapters($logEntries),
                'json',
                SerializationContext::create()->setGroups([
                    'Default',
                    'user_list',
                ]))
        );
    }

    /**
     * @param int $id
     *
     * @return \App\Entity\Bug|object
     */
    private function getBug(int $id): Bug
    {
        $bug = $this->getBugRepository()->find($id);

        if (empty($bug)) {
            throw new NotFoundHttpException('Bug not found.');
        }

        return $bug;
    }

    /**
     * @param int                 $id
     * @param \App\Entity\Tracker $tracker
     *
     * @return \App\Entity\Security\ApiUser
     */
    private function getDeveloper(int $id, Tracker $tracker): ApiUser
    {
        $users = $tracker->getDevelopers()->filter(function (ApiUser $user) use ($id) {
            return $user->isDeveloper() && $user->getId() === $id;
        });

        if ($users->isEmpty()) {
            throw new NotFoundHttpException('Developer not found in the tracker');
        }

        return $users->first();
    }

    /**
     * @param int $id
     *
     * @return \App\Entity\Tracker|object
     */
    private function getTracker(int $id): Tracker
    {
        $tracker = $this->getTrackerRepository()->find($id);

        if (empty($tracker)) {
            throw new NotFoundHttpException('Tracker not found.');
        }

        return $tracker;
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
     * @return \Gedmo\Loggable\Entity\Repository\LogEntryRepository
     */
    private function getLogEntryRepository(): LogEntryRepository
    {
        return $this->getDoctrine()->getRepository(LogEntry::class);
    }
}
