<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Security;

use App\Entity\Security\ApiUser;
use App\Repository\Security\ApiUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class TokenAuthenticator
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * TokenAuthenticator constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     *
     * @return JsonResponse|Response
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse([
            'errors' => [
                'security.unauthorized',
                ]
            ], Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    /**
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request): ?array
    {
        if ($token = $request->headers->get('X-AUTH-TOKEN') === null) {
            return null;
        }

        return [
            'token' => $request->headers->get('X-AUTH-TOKEN'),
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|void|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        return $this->getUserRepository()->getUserByToken($credentials['token']);
    }

    /**
     * @param mixed $credentials
     * @param ApiUser $user
     *
     * @return bool
     * @throws \Exception
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $user->validateToken($credentials['token']);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response|void|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'errors' => [
                'security.forbidden',
            ]
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     *
     * @return Response|void|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    /**
     * @return bool|void
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }

    /**
     * @return \App\Repository\Security\ApiUserRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->em->getRepository(ApiUser::class);
    }
}
