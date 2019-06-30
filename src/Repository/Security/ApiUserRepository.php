<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\Security;


use App\Entity\Security\ApiUser;
use Doctrine\ORM\EntityRepository;

/**
 * Class ApiUserRepository
 */
class ApiUserRepository extends EntityRepository
{
    /**
     * @param string $token
     *
     * @return ApiUser
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserByToken(string $token): ?ApiUser
    {
        $qb = $this->createQueryBuilder('o');

        $qb->where('o.token.value = :token')
            ->setParameter('token', $token);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
