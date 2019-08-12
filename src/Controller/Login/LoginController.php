<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Controller\Login;


use App\Entity\Security\ApiUser;
use App\Repository\Security\ApiUserRepository;
use App\Request\Security\AuthenticationRequest;
use App\Response\Security\AuthMethodResponseObject;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class LoginController
 *
 * @SWG\Tag(name="Login")
 */
class LoginController extends AbstractController
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * LoginController constructor.
     *
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     */
    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->serializer = $serializer;
        $this->em = $em;
    }

    /**
     * @Route("/", name="index", methods={"get"})
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns list of avalible auth methods.",
     *     @SWG\Schema(
     *      type="array",
     *      @SWG\Items(ref=@Model(type=AuthMethodResponseObject::class))
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function indexAction(): JsonResponse
    {
        $authMethods = [
            new AuthMethodResponseObject('github', $this->generateUrl('login_github_connect', [], UrlGeneratorInterface::ABSOLUTE_URL)),
        ];

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($authMethods, 'json')
        );
    }

    /**
     * @Route("/auth_by_code/", name="auth", methods={"post"})
     *
     * @param AuthenticationRequest $request
     *
     *
     * @SWG\Response(
     *     response="200",
     *     description="Auth user by code. Returns user with access token.",
     *     @Model(type=ApiUser::class, groups={"user_auth", "user_details"}),
     * )
     * @SWG\Response(
     *     response="401",
     *     description="Unable to authorize user by code"
     * )
     * @SWG\Parameter(
     *     name="Authentication request",
     *     in="body",
     *     allowEmptyValue=false,
     *     @Model(type=AuthenticationRequest::class)
     * )
     *
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function authByCodeAction(AuthenticationRequest $request): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUserRepository()->findOneBy(['code' => $request->getCode()]);

        if (empty($user)) {
            throw new AuthenticationException();
        }

        $user->createToken();

        $this->getDoctrine()->getManager()->flush();

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($user, 'json', SerializationContext::create()->setGroups([
                'user_auth', 'user_details'
            ]))
        );
    }

    /**
     * @return \App\Repository\Security\ApiUserRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->em->getRepository(ApiUser::class);
    }

}
