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
use App\Repository\Security\ApiUserRepository;
use App\Request\Project\CreateProjectRequest;
use App\Request\Project\DeveloperProjectInteractionRequest;
use App\Request\Project\UpdateProjectRequest;
use Doctrine\Common\Persistence\ObjectManager;
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
 * Class ProjectController
 *
 * @Route("/project", name="project_")
 *
 * @SWG\Tag(name="Project")
 */
class ProjectController extends AbstractController
{
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
     *      type="array",
     *      @SWG\Items(ref=@Model(type=Project::class, groups={"project_list"}))
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        $projects = $this->getProjectRepository()->findAll();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($projects, 'json', SerializationContext::create()->setGroups([
                'project_list',
            ]))
        );
    }

    /**
     * @Route("/{id}/", methods={"get"}, name="show")
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed information about the project.",
     *     @Model(type=Project::class, groups={"project_details", "tracker_list", "user_list"})
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
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($project, 'json', SerializationContext::create()->setGroups([
                'project_details',
                'tracker_list',
                'user_list',
            ]))
        );
    }

    /**
     * @Route("/", methods={"post"}, name="create")
     *
     * @param CreateProjectRequest $request
     *
     * @SWG\Response(
     *     response="201",
     *     description="Creates a project.",
     *     @Model(type=Project::class, groups={"project_details", "tracker_list", "user_list"})
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
     *     @Model(type=CreateProjectRequest::class)
     * )
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function createAction(CreateProjectRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var ApiUser $author */
        $author  = $this->getUser();
        $project = $author->createProject($request->getTitle(), null, $request->getLinks());

        if (null !== $developerIds = $request->getDevelopers()) {
            $developers = $this->getUserRepository()->getUsersByIds($developerIds);

            if (count($developerIds) !== count($developers)) {
                throw new NotFoundHttpException('Developer(s) not found');
            }

            foreach ($developers as $developer) {
                $project->addDeveloper($developer);
            }
        }

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($project, 'json', SerializationContext::create()->setGroups([
                'project_details',
                'tracker_list',
                'user_list',
            ])), Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}/", methods={"put"}, name="update")
     *
     * @param int                  $id
     * @param UpdateProjectRequest $request
     *
     * @SWG\Response(
     *     response="200",
     *     description="Updates the project.",
     *     @Model(type=Project::class, groups={"project_details", "tracker_list", "user_list"})
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
     *     @Model(type=CreateProjectRequest::class)
     * )
     *
     * @return JsonResponse
     */
    public function updateAction(int $id, UpdateProjectRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var Project $project */
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        $project->updateFromRequest($request);

        $this->getEntityManager()->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($project, 'json', SerializationContext::create()->setGroups([
                'project_details',
                'tracker_list',
                'user_list',
            ]))
        );
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

        /** @var Project $project */
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($project);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/add_developer/", methods={"patch"}, name="add_developer")
     *
     * @param int                                $id
     * @param DeveloperProjectInteractionRequest $request
     *
     * @SWG\Response(
     *     response="200",
     *     description="Add developer to the project.",
     *     @Model(type=Project::class, groups={"project_details", "tracker_list", "user_list"})
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project or developer not found."
     * )
     *
     * @SWG\Parameter(
     *     name="developer",
     *     description="Developer's id",
     *     in="query",
     *     type="string",
     *     required=true,
     * )
     *
     * @return JsonResponse
     */
    public function addDeveloperAction(int $id, DeveloperProjectInteractionRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var Project $project */
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        /** @var ApiUser $developer */
        $developer = $this->getUserRepository()->find($request->getDeveloper());

        if (empty($developer)) {
            throw new NotFoundHttpException('Developer not found');
        }
        $project->addDeveloper($developer);

        $this->getEntityManager()->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($project, 'json', SerializationContext::create()->setGroups([
                'project_details',
                'tracker_list',
                'user_list',
            ]))
        );
    }

    /**
     * @Route("/{id}/remove_developer/", methods={"patch"}, name="remove_developer")
     *
     * @param int                                $id
     * @param DeveloperProjectInteractionRequest $request
     *
     * @SWG\Response(
     *     response="200",
     *     description="Add developer to the project.",
     *     @Model(type=Project::class, groups={"project_details", "tracker_list", "user_list"})
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project or developer not found."
     * )
     *
     * @SWG\Parameter(
     *     name="developer",
     *     description="Developer's id",
     *     in="query",
     *     type="string",
     *     required=true,
     * )
     *
     * @return JsonResponse
     */
    public function removeDeveloperAction(int $id, DeveloperProjectInteractionRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_QA');

        /** @var Project $project */
        $project = $this->getProjectRepository()->find($id);

        if (empty($project)) {
            throw new NotFoundHttpException('Project not found');
        }

        /** @var ApiUser $developer */
        $developer = $this->getUserRepository()->find($request->getDeveloper());

        if (empty($developer)) {
            throw new NotFoundHttpException('Developer not found');
        }

        $project->removeDeveloper($developer);

        $this->getEntityManager()->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($project, 'json', SerializationContext::create()->setGroups([
                'project_details',
                'tracker_list',
                'user_list',
            ]))
        );
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

    /**
     * @return \App\Repository\Security\ApiUserRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->getDoctrine()->getRepository(ApiUser::class);
    }
}
