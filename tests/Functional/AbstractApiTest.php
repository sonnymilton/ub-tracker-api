<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use App\Entity\Security\ApiUser;
use App\Tests\Tools\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Abstract api test
 */
abstract class AbstractApiTest extends WebTestCase
{
    const AUTH_PARAMETER_NAME = 'HTTP_X-AUTH-TOKEN';

    /**
     * @var array
     */
    protected static $roleTokenMap;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected static $client;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        self::$client = self::createClient([], [
            'HTTP_Content-Type' => 'application/json',
        ]);

        DatabasePrimer::prime(self::$container);

        self::$roleTokenMap = self::getRoleTokenMap(self::$container->get('doctrine.orm.default_entity_manager'));
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     *
     * @return array
     */
    protected static function getRoleTokenMap(EntityManagerInterface $em): array
    {
        /** @var \App\Repository\Security\ApiUserRepository $repository */
        $repository = $em->getRepository(ApiUser::class);
        $qb         = $repository->createQueryBuilder('o');

        $groupedTokens = $qb
            ->select('o.roles')
            ->addSelect('o.token.value')
            ->groupBy('o.roles')
            ->getQuery()
            ->getResult();

        $roleTokenMap = [];

        foreach ($groupedTokens as $groupToken) {
            $role                = strtolower(str_replace('ROLE_', '', current($groupToken['roles'])));
            $roleTokenMap[$role] = $groupToken['token.value'];
        }

        return $roleTokenMap;
    }
}
