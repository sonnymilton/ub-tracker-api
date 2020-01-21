<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\API;

use App\Entity\BugReport\BugReport;
use App\Entity\Comment;
use App\Repository\BugReportRepository;
use App\Repository\CommentRepository;
use App\Request\Comment\CommentRequest;
use App\Serializer\AutoserializationTrait;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Comment controller
 *
 * @Route(name="comment_")
 *
 * @SWG\Tag(name="Comment")
 */
class CommentController extends AbstractController
{
    const LIST_SERIALIZATION_GROUPS    = ['comment_list', 'user_list'];
    const DETAILS_SERIALIZATION_GROUPS = ['comment_details', 'user_list'];
    use AutoserializationTrait;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * CommentController constructor.
     *
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/bug_report/{id}/comment/", name="create", methods={"POST"})
     *
     * @SWG\Response(
     *     response="201",
     *     description="Creates comment for bug report.",
     *     @Model(type=Comment::class, groups=CommentController::DETAILS_SERIALIZATION_GROUPS)
     * )
     * @SWG\Response(
     *     response="404",
     *     description="BugReport report not found."
     * )
     *
     * @SWG\Parameter(
     *     name="Create comment request",
     *     required=true,
     *     in="body",
     *     @Model(type=CommentRequest::class)
     * )
     *
     * @param int                                 $id
     * @param \App\Request\Comment\CommentRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \Exception
     */
    public function createAction(int $id, CommentRequest $request): JsonResponse
    {
        /** @var \App\Entity\Security\ApiUser $author */
        $author    = $this->getUser();
        $bugReport = $this->getBugReport($id);

        $comment = $author->createComment($bugReport, $request->getText());

        $this->getEntityManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($comment));
    }

    /**
     * @Route("/comment/{id}/", name="update", methods={"PUT"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Updates comment.",
     *     @Model(type=Comment::class, groups=CommentController::DETAILS_SERIALIZATION_GROUPS)
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Comment not found."
     * )
     *
     * @SWG\Parameter(
     *     name="Update comment request",
     *     required=true,
     *     in="body",
     *     @Model(type=CommentRequest::class)
     * )
     *
     * @param int                                 $id
     * @param \App\Request\Comment\CommentRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(int $id, CommentRequest $request): JsonResponse
    {
        $comment = $this->getComment($id);

        $this->denyAccessUnlessGranted('edit', $comment);

        $comment->update($request->getText());

        $this->getEntityManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($comment));
    }

    /**
     * @Route("/comment/{id}/", name="delete", methods={"delete"})
     *
     * @SWG\Response(
     *     response="204",
     *     description="Removes comment"
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Comment not found."
     * )
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(int $id): Response
    {
        $comment = $this->getComment($id);

        $this->denyAccessUnlessGranted('delete', $comment);

        $em = $this->getEntityManager();

        $em->remove($comment);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param int $id
     *
     * @return \App\Entity\BugReport\BugReport
     */
    private function getBugReport(int $id): BugReport
    {
        /** @var \App\Entity\BugReport\BugReport $bugReport */
        $bugReport = $this->getBugReportRepository()->find($id);

        if (empty($bugReport)) {
            throw new NotFoundHttpException('BugReport not found.');
        }

        return $bugReport;
    }

    private function getComment(int $id): Comment
    {
        /** @var Comment $comment */
        $comment = $this->getCommentRepository()->find($id);

        if (empty($comment)) {
            throw new NotFoundHttpException('Comment not found.');
        }

        return $comment;
    }

    /**
     * @return \App\Repository\BugReportRepository
     */
    private function getBugReportRepository(): BugReportRepository
    {
        return $this->getDoctrine()->getRepository(BugReport::class);
    }

    /**
     * @return \App\Repository\CommentRepository
     */
    private function getCommentRepository(): CommentRepository
    {
        return $this->getDoctrine()->getRepository(Comment::class);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getEntityManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }
}
