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

use App\Entity\Tracker;
use App\Repository\TrackerRepository;
use App\Tests\Functional\AbstractApiTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remove tracker test
 */
class RemoveTrackerTest extends AbstractApiTest
{
    public function testNotAdminCantDeleteTracker(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(Request::METHOD_DELETE, sprintf('/api/tracker/%d/', $this->getExistingTrackerId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testDeleteTracker(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['admin']);

        $client->request(Request::METHOD_DELETE, sprintf('/api/tracker/%d/', $this->getExistingTrackerId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testDeleteNotExistingTracker(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['admin']);

        $client->request(Request::METHOD_DELETE, sprintf('/api/tracker/%d/', 139012930321093));

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
