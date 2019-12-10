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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Toggle tracker status test
 */
class ToggleTrackerStatusTest extends AbstractApiTest
{
    /**
     * @param string $urlTemplate
     *
     * @dataProvider provideUrlTemplates
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testDeveloperCantToggleTrackerStatus(string $urlTemplate): void
    {
        $this->expectException(AccessDeniedException::class);
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);
        $client->catchExceptions(false);

        $client->request(Request::METHOD_PATCH, sprintf($urlTemplate, $this->getExistingTrackerId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @param string $urlTemplate
     *
     * @dataProvider provideUrlTemplates
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testToggleTrackerStatus(string $urlTemplate): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(Request::METHOD_PATCH, sprintf($urlTemplate, $this->getExistingTrackerId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @param string $urlTemplate
     *
     * @dataProvider provideUrlTemplates
     */
    public function testToggleNotExistingTrackerStatus(string $urlTemplate): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(Request::METHOD_PATCH, sprintf($urlTemplate, 1239012903));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * @return \Generator
     */
    public function provideUrlTemplates(): \Generator
    {
        yield 'close' => ['/api/tracker/%d/close/'];
        yield 'open' => ['/api/tracker/%d/open/'];
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
