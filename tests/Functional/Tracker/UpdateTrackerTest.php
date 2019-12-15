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
use App\Request\UnableToProcessRequestObjectException;
use App\Tests\Functional\AbstractApiTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Update tracker test
 */
class UpdateTrackerTest extends AbstractApiTest
{
    public function testDeveloperCantUpdateTracker(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::$client;
        $client->catchExceptions(false);
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(Request::METHOD_PUT, sprintf('/api/tracker/%d/', $this->getExistingTrackerId()));

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @param array $developers
     * @param array $links
     *
     * @dataProvider provideUpdateTrackerData
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testUpdateTracker(?array $developers, ?array $links): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(
            Request::METHOD_PUT,
            sprintf('/api/tracker/%d/', $this->getExistingTrackerId()), [], [], [], json_encode(array_filter([
                    'developers' => $developers,
                    'links'      => $links,
                ], function ($value) {
                    return null !== $value;
                })
            )
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());
    }

    /**
     * @param array|null $developers
     * @param array|null $links
     * @param int        $expectedCode
     *
     * @dataProvider provideUpdateTrackerInvalidData
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testCreateTrackerWithInvalidData(?array $developers, ?array $links, int $expectedCode = Response::HTTP_BAD_REQUEST): void
    {
        if ($expectedCode === Response::HTTP_UNPROCESSABLE_ENTITY) {
            $this->expectException(UnableToProcessRequestObjectException::class);
        }

        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);
        $client->catchExceptions(false);

        $client->request(
            Request::METHOD_PUT,
            sprintf('/api/tracker/%d/', $this->getExistingTrackerId()), [], [], [], json_encode(array_filter([
                    'developers' => $developers,
                    'links'      => $links,
                ], function ($value) {
                    return null !== $value;
                })
            )
        );

        $response = $client->getResponse();

        $this->assertEquals($expectedCode, $response->getStatusCode(), $response->getContent());
    }

    /**
     * @return \Generator
     */
    public function provideUpdateTrackerData(): \Generator
    {
        yield 'With all data' => [
            [3, 4],
            [['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api/']],
        ];

        yield 'Without links' => [
            [3, 4],
            null,
        ];

        yield 'Without data' => [null, null];
    }

    /**
     * @return \Generator
     */
    public function provideUpdateTrackerInvalidData(): \Generator
    {
        yield 'With invalid  url in link' => [
            [3, 4],
            [['title' => 'github', 'url' => 'invalid url']],
        ];

        yield 'With not a developer' => [
            [3, 1],
            [['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api/']],
            Response::HTTP_UNPROCESSABLE_ENTITY,
        ];
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
