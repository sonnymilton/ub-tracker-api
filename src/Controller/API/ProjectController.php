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
use App\Repository\ProjectRepository;
use App\Request\Project\ProjectRequest;
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
 * Class ProjectController
 *
 * @Route("/project", name="project_")
 *
 * @SWG\Tag(name="Project")
 */
class ProjectController extends AbstractController
{
    const LIST_SERIALIZATION_GROUPS    = ['project_list'];
    const DETAILS_SERIALIZATION_GROUPS = ['project_details', 'tracker_list', 'user_list'];
    use AutoserializationTrait;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ProjectController constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", methods={"get"}, name="list")
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns the list of projects.",
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(ref="#/definitions/ProjectFromList")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        $projects = $this->getProjectRepository()->findAll();

        return JsonResponse::fromJsonString($this->autoserialize($projects));
    }

    /**
     * @Route("/{id}/", methods={"get"}, name="show")
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed information about the project.",
     *     @SWG\Schema(ref="#/definitions/Project")
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project not found.",
     * )
     *
     * @return JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $project = $this->getProject($id);

        return JsonResponse::fromJsonString($this->autoserialize($project));
    }

    /**
     * @Route("/", methods={"post"}, name="create")
     *
     * @param \App\Request\Project\ProjectRequest $request
     *
     * @SWG\Response(
     *     response="201",
     *     description="Creates a project.",
     *     @SWG\Schema(ref="#/definitions/Project")
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Developer(s) not found."
     * )
     *
     * @SWG\Parameter(
     *     name="Create project request.",
     *     in="body",
     *     allowEmptyValue=false,
     *     @Model(type=ProjectRequest::class)
     * )
     *                                                    *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function createAction(ProjectRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var ApiUser $author */
        $author  = $this->getUser();
        $project = $author->createProject(
            $request->getTitle(),
            $request->getLocales(),
            $request->getLinks()
        );

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        return JsonResponse::fromJsonString($this->autoserialize($project), Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}/", methods={"put"}, name="update")
     *
     * @param int                                 $id
     * @param \App\Request\Project\ProjectRequest $request
     *
     * @return JsonResponse
     * @SWG\Response(
     *     response="200",
     *     description="Updates the project.",
     *     @SWG\Schema(ref="#/definitions/Project")
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project not found."
     * )
     *
     * @SWG\Parameter(
     *     name="Update project request",
     *     in="body",
     *     allowEmptyValue=false,
     *     @Model(type=ProjectRequest::class)
     * )
     */
    public function updateAction(int $id, ProjectRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        $project = $this->getProject($id);

        $project->updateFromRequest($request);

        $this->getEntityManager()->flush();

        return JsonResponse::fromJsonString($this->autoserialize($project));
    }

    /**
     * @Route("/{id}/", name="remove", methods={"delete"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="204",
     *     description="Removes the project."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project not found."
     * )
     *
     * @return Response
     */
    public function removeAction(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $project = $this->getProject($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($project);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param int $id
     *
     * @return \App\Entity\Project|object
     */
    private function getProject(int $id)
    {
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found.');
        }

        return $project;
    }

    /**
     * @return ObjectManager
     */
    private function getEntityManager(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \App\Repository\ProjectRepository
     */
    private function getProjectRepository(): ProjectRepository
    {
        return $this->getDoctrine()->getRepository(Project::class);
    }
}
