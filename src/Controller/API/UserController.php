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

use App\Entity\Security\ApiUser;
use App\Repository\Security\ApiUserRepository;
use App\Serializer\AutoserializationTrait;
use JMS\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User controller
 *
 * @Route("/user", name="user_")
 *
 * @SWG\Tag(name="User")
 */
class UserController extends AbstractController
{
    const LIST_SERIALIZATION_GROUPS    = ['user_list'];
    const DETAILS_SERIALIZATION_GROUPS = ['user_details'];
    use AutoserializationTrait;

    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * UserController constructor.
     *
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="list", methods={"GET"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns the list of users.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref="#/definitions/UserFromList"))
     *     )
     * )
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listAction(): JsonResponse
    {
        $users = $this->getUserRepository()->findAll();

        return JsonResponse::fromJsonString($this->autoserialize($users));
    }

    /**
     * @Route("/{id}/", name="show", methods={"GET"})
     *
     * @param int $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns detailed info abour the user",
     *     @SWG\Schema(ref="#/definitions/UserFromList")
     * )
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $user = $this->getUserRepository()->find($id);

        if (empty($user)) {
            throw new NotFoundHttpException('User not found');
        }

        return JsonResponse::fromJsonString($this->autoserialize($user));
    }

    /**
     * @return \App\Repository\Security\ApiUserRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->getDoctrine()->getRepository(ApiUser::class);
    }
}
