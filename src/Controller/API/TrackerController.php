<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\API;

use App\Entity\Project;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use App\Repository\TrackerRepository;
use App\Serializer\AutoserializationTrait;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TrackerController
 *
 * @Route(name="tracker_")
 *
 * @SWG\Tag(name="Tracker")
 */
class TrackerController extends AbstractController
{
    const LIST_SERIALIZATION_GROUPS    = ['tracker_list', 'user_list'];
    const DETAILS_SERIALIZATION_GROUPS = ['tracker_show', 'user_list', 'bug_list', 'project_list'];
    use AutoserializationTrait;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * TrackerController constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/tracker/{id}/", name="show", methods={"get"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed information about the tracker.",
     *     @SWG\Schema(ref="#/definitions/Tracker")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Tracker not found."
     * )
     *
     * @return JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $tracker = $this->getTracker($id);

        return JsonResponse::fromJsonString($this->autoserialize($tracker));
    }

    /**
     * @Route("/project/{id}/tracker/{position}/", name="show_by_position", methods={"get"})
     *
     * @param int $id
     * @param int $position
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed info about the tracker by position in the project.",
     *     @SWG\Schema(ref="#/definitions/Tracker")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project or tracker not found"
     * )
     *
     * @return JsonResponse|Response
     */
    public function showTrackerByPositionAction(int $id, int $position): JsonResponse
    {
        $project = $this->getProject($id);

        $trackers = $this->getTrackerRepository()->getTrackersForProject($project);

        $position -= 1;

        if (!isset($trackers[$position])) {
            throw new NotFoundHttpException(sprintf('Tracker with position %d not found in this project', $position));
        }

        return $this->forward('App\Controller\API\TrackerController::showAction', [
            'id' => $trackers[$position]->getId(),
        ]);
    }

    /**
     * @Route("/project/{id}/tracker/", name="create_tracker", methods={"post"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Creates new tracker in specified project.",
     *     @SWG\Schema(ref="#/definitions/Tracker")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project not found."
     * )
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function createTrackerAction(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $project = $this->getProject($id);

        /** @var ApiUser $user */
        $user = $this->getUser();

        $tracker = $user->createTracker($project);
        $project->addTracker($tracker);

        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        return JsonResponse::fromJsonString($this->autoserialize($tracker), Response::HTTP_CREATED);
    }

    /**
     * @Route("/tracker/{id}/", name="delete", methods={"delete"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="204",
     *     description="Removes the tracker.",
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Tracker not found.",
     * )
     *
     * @return Response
     */
    public function removeAction(int $id): Response
    {
        $tracker = $this->getTracker($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($tracker);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/tracker/{id}/close/", name="close", methods={"patch"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Closes the tracker.",
     *     @SWG\Schema(ref="#/definitions/Tracker")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Tracker not found."
     * )
     *
     * @return JsonResponse
     */
    public function closeAction(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $tracker = $this->getTracker($id);

        $tracker->close();
        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($tracker));
    }

    /**
     * @Route("/tracker/{id}/open/", name="open", methods={"patch"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Opens the tracker.",
     *     @SWG\Schema(ref="#/definitions/Tracker")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Tracker not found."
     * )
     *
     * @return JsonResponse
     */
    public function openAction(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $tracker = $this->getTracker($id);

        $tracker->open();
        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($tracker));
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
     * @param int $id
     *
     * @return \App\Entity\Project|object
     */
    private function getProject(int $id): Project
    {
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found.');
        }

        return $project;
    }

    /**
     * @return \App\Repository\TrackerRepository
     */
    private function getTrackerRepository(): TrackerRepository
    {
        return $this->getDoctrine()->getRepository(Tracker::class);
    }

    /**
     * @return \App\Repository\ProjectRepository
     */
    private function getProjectRepository()
    {
        return $this->getDoctrine()->getRepository(Project::class);
    }
}
