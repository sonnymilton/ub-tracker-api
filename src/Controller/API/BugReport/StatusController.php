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
use App\Repository\BugReportRepository;
use Doctrine\Common\Collections\Criteria;
use Gedmo\Loggable\Entity\LogEntry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BugReport report status controller
 *
 * @Route("/bug_report/{id}",  name="bug_report_change_status_")
 *
 * @SWG\Tag(name="Bug report")
 */
class StatusController extends AbstractController
{
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * StatusController constructor.
     *
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/cant_be_reproduced/", name="cant_be_reproduced", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug report status to can't be reproduced",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only responsible can close the bug report."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cantBeReproducedAction(int $id): JsonResponse
    {
        $bugReport = $this->getBugReport($id);

        $this->denyAccessUnlessGranted('cant_be_reproduced', $bugReport);

        $bugReport->cantBeReproduced();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/close/", name="close", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug report status to close.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only QA can close the bug report.",
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function closeAction(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $bugReport = $this->getBugReport($id);

        $bugReport->close();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/return/", name="return", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug report status to returned.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only bug reports that are sent for verify can be returned by QA."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function returnAction(int $id): JsonResponse
    {
        $bugReport = $this->getBugReport($id);

        $this->denyAccessUnlessGranted('return', $bugReport);

        $bugReport->bugReturn();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/verify/", name="verify", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug report status to verified.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only bug reports that are sent for verify can be verified by QA."
     * )
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function verifyAction(int $id): JsonResponse
    {
        $bugReport = $this->getBugReport($id);

        $this->denyAccessUnlessGranted('verify', $bugReport);

        $bugReport->verify();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/send_to_discuss/", name="send_to_discuss", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug report status to sent to discuss",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only responsible person can send the bug report to discuss"
     * )
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendToDiscussAction(int $id)
    {
        $bugReport = $this->getBugReport($id);

        $this->denyAccessUnlessGranted('send_to_discuss', $bugReport);

        $bugReport->sendToDiscuss();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/send_to_verify/", name="send_to_verify", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug report status to sent to verify",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only responsible person can send the bug report to verify"
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendToVerifyAction(int $id)
    {
        $bugReport = $this->getBugReport($id);

        $this->denyAccessUnlessGranted('send_to_verify', $bugReport);

        $bugReport->sendToVerify();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/reopen/", name="reopen", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Re-opens closed bug report.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only closed or verified bug reports can be returned"
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function reopenAction(int $id): JsonResponse
    {
        $bugReport = $this->getBugReport($id);

        $this->denyAccessUnlessGranted('reopen', $bugReport);

        $bugReport->reopen();

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @Route("/undo_status_change/", name="undo", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Cancels last bug report's status change.",
     *     @SWG\Schema(ref="#/definitions/BugReport")
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only the user, who last changed status can undo this action"
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function undoAction(int $id): JsonResponse
    {
        $bugReport    = $this->getBugReport($id);
        $bugReportLog = $this->getLatestLogEntry($bugReport);

        $this->denyAccessUnlessGranted('undo', $bugReportLog);

        $bugReport->undoStatusChange($bugReportLog);

        $this->getEntityManager()->flush();

        return $this->createResponse($bugReport);
    }

    /**
     * @param \App\Entity\BugReport\BugReport $bugReport
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function createResponse(BugReport $bugReport): JsonResponse
    {
        return JsonResponse::fromJsonString(
            $this->serializer->serialize($bugReport, 'json', SerializationContext::create()->setGroups([
                'bugreport_details',
                'user_list',
                'tracker_list',
                'comment_list',
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
     * @param \App\Entity\BugReport\BugReport $bugReport
     *
     * @return \Gedmo\Loggable\Entity\LogEntry
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getLatestLogEntry(BugReport $bugReport): LogEntry
    {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this
            ->getEntityManager()
            ->getRepository(LogEntry::class)
            ->createQueryBuilder('log_entry');

        $result = $qb->where('log_entry.objectClass = :object_class')
            ->andWhere('log_entry.objectId = :object_id')
            ->orderBy('log_entry.loggedAt', Criteria::DESC)
            ->setParameter('object_class', BugReport::class)
            ->setParameter('object_id', $bugReport->getId())
            ->setMaxResults(1)
            ->setFirstResult(1)
            ->getQuery()
            ->getResult();

        if (empty($result)) {
            throw new ConflictHttpException('Unable to undo status change');
        }

        return reset($result);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \App\Repository\BugReportRepository
     */
    private function getBugReportRepository(): BugReportRepository
    {
        return $this->getDoctrine()->getRepository(BugReport::class);
    }
}
