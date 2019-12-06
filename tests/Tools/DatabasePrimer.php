<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Tools;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Database primer
 */
class DatabasePrimer
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public static function prime(ContainerInterface $container)
    {
        if ('test' !== $container->getParameter('kernel.environment')) {
            throw new \LogicException('Primer must be executed in the test environment');
        }

        $entityManager = $container->get('doctrine.orm.entity_manager');

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadata);

        $fixturesLoader = $container->get('doctrine.fixtures.loader');
        $ormExecutor = new ORMExecutor($entityManager, new ORMPurger($entityManager));

        $fixtures = $fixturesLoader->getFixtures();

        $ormExecutor->execute($fixtures);
    }
}
