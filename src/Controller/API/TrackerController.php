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
use App\Entity\Tracker;
use App\Repository\ProjectRepository;
use App\Repository\TrackerRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TrackerController
 *
 * @Route("/tracker", name="tracker_")
 *
 * @SWG\Tag(name="Tracker")
 */
class TrackerController extends AbstractController
{
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
     * @Route("/{id}/", name="show", methods={"get"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed information about the tracker.",
     *     @Model(type=Tracker::class, groups={"tracker_show", "user_list", "bug_list"})
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Project or tracker not found"
     * )
     *
     * @return JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $tracker = $this->getTrackerRepository()->find($id);

        if (empty($tracker)) {
            throw new NotFoundHttpException('Tracker not found');
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($tracker, 'json', SerializationContext::create([
                'tracker_show',
            ]))
        );
    }


    /**
     * @return \App\Repository\TrackerRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getTrackerRepository(): TrackerRepository
    {
        return $this->getDoctrine()->getRepository(Tracker::class);
    }

    /**
     * @return \App\Repository\ProjectRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getProjectRepository(): ProjectRepository
    {
        return $this->getDoctrine()->getRepository(Project::class);
    }
}
