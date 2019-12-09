<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional\Tracker;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\Functional\AbstractApiTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Create tracker test
 */
class CreateTrackerTest extends AbstractApiTest
{
    public function testDeveloperCantCreateTracker(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::$client;
        $client->catchExceptions(false);
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(Request::METHOD_POST, sprintf('/api/project/%d/tracker/', $this->getExistingProjectId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testCreateTracker(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(Request::METHOD_POST, sprintf('/api/project/%d/tracker/', $this->getExistingProjectId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateTrackerForNotExistingProject(): void
    {
        $client = self::$client;

        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(Request::METHOD_POST, sprintf('/api/project/%d/tracker/', 2052050200));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getExistingProjectId(): int
    {
        return $this
            ->getProjectRepository()
            ->createQueryBuilder('o')
            ->select('o.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \App\Repository\ProjectRepository
     */
    private function getProjectRepository(): ProjectRepository
    {
        return self::$container->get('doctrine.orm.default_entity_manager')->getRepository(Project::class);
    }
}
