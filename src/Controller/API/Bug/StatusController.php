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
use App\Repository\BugRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Status controller
 *
 * @Route("/bug/{id}",  name="bug_change_status_")
 *
 * @SWG\Tag(name="Bug")
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
     *     description="Changes bug status to can't be reproduced",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"}),
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only responsible can close the bug."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cantBeReproducedAction(int $id): JsonResponse
    {
        $bug = $this->getBug($id);

        $this->denyAccessUnlessGranted('cant_be_reproduced', $bug);

        $bug->cantBeReproduced();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @Route("/close/", name="close", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug status to close.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only QA can close the bug.",
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function closeAction(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $bug = $this->getBug($id);

        $bug->close();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @Route("/return/", name="return", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug status to returned.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only bugs that are sent for verify can be returned by QA."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function returnAction(int $id): JsonResponse
    {
        $bug = $this->getBug($id);

        $this->denyAccessUnlessGranted('return', $bug);

        $bug->bugReturn();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @Route("/verify/", name="verify", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug status to verified.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only bugs that are sent for verify can be verified by QA."
     * )
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function verifyAction(int $id): JsonResponse
    {
        $bug = $this->getBug($id);

        $this->denyAccessUnlessGranted('verify', $bug);

        $bug->verify();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @Route("/send_to_discuss/", name="send_to_discuss", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug status to sent to discuss",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only responsible person can send the bug to discuss"
     * )
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendToDiscussAction(int $id)
    {
        $bug = $this->getBug($id);

        $this->denyAccessUnlessGranted('send_to_discuss', $bug);

        $bug->sendToDiscuss();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @Route("/send_to_verify/", name="send_to_verify", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Changes bug status to sent to verify",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only responsible person can send the bug to verify"
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendToVerifyAction(int $id)
    {
        $bug = $this->getBug($id);

        $this->denyAccessUnlessGranted('send_to_verify', $bug);

        $bug->sendToVerify();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @Route("/reopen/", name="reopen", methods={"PATCH"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Re-opens closed bug.",
     *     @Model(type=Bug::class, groups={"bug_details", "user_list", "tracker_list"})
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Only closed or verified bugs can be returned"
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function reopenAction(int $id): JsonResponse
    {
        $bug = $this->getBug($id);

        $this->denyAccessUnlessGranted('reopen', $bug);

        $bug->reopen();

        $this->getEntityManager()->flush();

        return $this->createResponse($bug);
    }

    /**
     * @param \App\Entity\Bug $bug
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function createResponse(Bug $bug): JsonResponse
    {
        return JsonResponse::fromJsonString(
            $this->serializer->serialize($bug, 'json', SerializationContext::create()->setGroups([
                'bug_details',
                'user_list',
                'tracker_list',
                'comment_list',
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
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \App\Repository\BugRepository
     */
    private function getBugRepository(): BugRepository
    {
        return $this->getDoctrine()->getRepository(Bug::class);
    }
}
