<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\API\BugReport;

use App\Entity\BugReport\BugReport;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use App\Log\BugReport\BugReportLogEntryAdapterFactory;
use App\Repository\TrackerRepository;
use App\Request\BugReport\CreateBugReportRequest;
use App\Request\BugReport\UpdateBugReportRequest;
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
 * Default bug report controller
 *
 * @Route(name="bug_report_")
 *
 * @SWG\Tag(name="Bug report")
 */
class DefaultController extends AbstractController
{
    const LIST_SERIALIZATION_GROUPS    = ['bugreport_details', 'user_list', 'tracker_list'];
    const DETAILS_SERIALIZATION_GROUPS = ['bugreport_details', 'project_list', 'user_list', 'tracker_list', 'comment_list'];

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
     * @Route("/tracker/{id}/bug_report/", name="create", methods={"post"})
     *
     * @SWG\Response(
     *     response="201",
     *     description="Creates bug report.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Tracker or developer not found."
     * )
     *
     * @SWG\Parameter(
     *     name="Create bug report request",
     *     required=true,
     *     in="body",
     *     @Model(type=CreateBugReportRequest::class)
     * )
     *
     * @param int                                           $id
     * @param \App\Request\BugReport\CreateBugReportRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    public function createAction(int $id, CreateBugReportRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $tracker   = $this->getTracker($id);
        $developer = $this->getDeveloper($request->getResponsiblePerson(), $tracker);

        /** @var ApiUser $author */
        $author = $this->getUser();

        $bugReportReport = $author->createBugReportFromRequest($request, $tracker, $developer);

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($bugReportReport));
    }

    /**
     * @Route("/bug_report/{id}", name="show", methods={"get"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed info about the bug report.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="BugReport report not found.",
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $bugReport = $this->getBugReport($id);

        return JsonResponse::fromJsonString($this->autoserialize($bugReport));
    }

    /**
     * @Route("/bug_report/{id}/", name="update", methods={"put"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Updates the bug report.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="BugReport report or developer not found.",
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
     *
     * @SWG\Parameter(
     *     name="Update bug report request",
     *     required=true,
     *     in="body",
     *     @Model(type=UpdateBugRequest::class)
     * )
     *
     * @param int                                           $id
     * @param \App\Request\BugReport\UpdateBugReportRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(int $id, UpdateBugReportRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $bugReport = $this->getBugReport($id);
        $tracker   = $bugReport->getTracker();
        $developer = $this->getDeveloper($request->getResponsiblePerson(), $tracker);

        $bugReport->updateFromRequest($request, $developer);

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($bugReport));
    }

    /**
     * @Route("/bug_report/{id}/", name="delete", methods={"delete"})
     *
     * @SWG\Response(
     *     response="204",
     *     description="Removes the bug report."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="BugReport report not found."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $bugReport = $this->getBugReport($id);

        $em = $this->getDoctrine()->getManager();

        $em->remove($bugReport);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/bug_report/{id}/history/",  name="history", methods={"GET"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns bug report's revision history."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="BugReport report not found."
     * )
     *
     * @param int                                                $id
     * @param \App\Log\BugReport\BugReportLogEntryAdapterFactory $logEntryAdapterFactory
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function historyAction(int $id, BugReportLogEntryAdapterFactory $logEntryAdapterFactory): JsonResponse
    {
        $bugReport  = $this->getBugReport($id);
        $logEntries = $this->getLogEntryRepository()->getLogEntries($bugReport);

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
     * @return \App\Entity\BugReport\BugReport|object
     */
    private function getBugReport(int $id): BugReport
    {
        $bugReport = $this->getBugReportRepository()->find($id);

        if (empty($bugReport)) {
            throw new NotFoundHttpException('BugReport not found.');
        }

        return $bugReport;
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
     * @return \App\Repository\BugReportRepository
     */
    private function getBugReportRepository()
    {
        return $this->getDoctrine()->getRepository(BugReport::class);
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
