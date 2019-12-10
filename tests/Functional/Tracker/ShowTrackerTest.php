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
use App\Entity\Tracker;
use App\Repository\ProjectRepository;
use App\Repository\TrackerRepository;
use App\Tests\Functional\AbstractApiTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Show tracker test
 */
class ShowTrackerTest extends AbstractApiTest
{
    public function testShowTrackerList(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);
        $client->request(Request::METHOD_GET, sprintf('/api/project/%d/', $this->getExistingProjectId()));

        $response = $client->getResponse();

        $data = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertIsArray($data->trackers);
    }

    public function testShowTracker(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);
        $client->request(Request::METHOD_GET, sprintf('/api/tracker/%d/', $this->getExistingTrackerId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testTrackerNotFound(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);
        $client->request(Request::METHOD_GET, sprintf('/api/tracker/%d/', 1656557787874));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @return int
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getExistingTrackerId(): int
    {
        return $this
            ->getTrackerRepository()
            ->createQueryBuilder('o')
            ->select('o.id')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return int
     *
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
        return $this->getEntityManager()->getRepository(Project::class);
    }

    /**
     * @return \App\Repository\TrackerRepository
     */
    private function getTrackerRepository(): TrackerRepository
    {
        return $this->getEntityManager()->getRepository(Tracker::class);
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return self::$container->get('doctrine.orm.default_entity_manager');
    }
}
