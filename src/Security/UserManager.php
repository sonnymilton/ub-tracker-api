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
use League\OAuth2\Client\Provider\GithubResourceOwner;

class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * UserManager constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param GithubResourceOwner $resourceOwner
     *
     * @return ApiUser
     */
    public function getUserByGithubResourceOwner(GithubResourceOwner $resourceOwner): ApiUser
    {
        $user = $this->getUserRepository()->findOneBy(['email' => $resourceOwner->getEmail()]);

        if (empty($user)) {
            $user = new ApiUser($resourceOwner->getNickname(), $resourceOwner->getEmail(), bin2hex(\openssl_random_pseudo_bytes(32)));
            $this->em->persist($user);
        }

        return $user;
    }

    /**
     * @return \App\Repository\Security\ApiUserRepository|\Doctrine\Common\Persistence\ObjectRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->em->getRepository(ApiUser::class);
    }
}
