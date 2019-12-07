<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional\Project;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\Functional\AbstractApiTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Update project test
 */
class UpdateProjectTest extends AbstractApiTest
{
    /**
     * @param string|null $title
     * @param array|null  $links
     * @param array|null  $locales
     *
     * @dataProvider provideUpdateProjectData
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testUpdateProject(?string $title, ?array $links, ?array $locales): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(
            Request::METHOD_PUT,
            sprintf('/api/project/%d/', $this->getExistingProjectId()),
            [],
            [],
            [],
            json_encode([
                'title'   => $title,
                'links'   => $links,
                'locales' => $locales,
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());
    }

    /**
     * @param string|null $title
     * @param array|null  $links
     * @param array|null  $locales
     *
     * @dataProvider provideUpdateProjectInvalidData
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testUpdateProjectWithInvalidData(?string $title, ?array $links, ?array $locales): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(
            Request::METHOD_PUT,
            sprintf('/api/project/%d/', $this->getExistingProjectId()),
            [],
            [],
            [],
            json_encode([
                'title'   => $title,
                'links'   => $links,
                'locales' => $locales,
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testUpdateNotExistingProject(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(
            Request::METHOD_PUT,
            sprintf('/api/project/%d/', 10230923192),
            [],
            [],
            [],
            json_encode([
                'title'   => 'Test',
                'links'   => [
                    ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
                ],
                'locales' => ['en'],
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeveloperCantUpdateProject(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::$client;
        $client->catchExceptions(false);
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(
            Request::METHOD_PUT,
            sprintf('/api/project/%d/', $this->getExistingProjectId()),
            [],
            [],
            [],
            json_encode([
                'title'   => 'Test',
                'links'   => [
                    ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
                ],
                'locales' => ['en'],
            ])
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @return \Generator
     */
    public function provideUpdateProjectData(): \Generator
    {
        yield 'With all data' => [
            'title'   => 'Test',
            'links'   => [
                ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
            ],
            'locales' => ['en'],
        ];

        yield 'Without links' => [
            'title'   => 'Test',
            null,
            'locales' => ['en'],
        ];
    }

    /**
     * @return \Generator
     */
    public function provideUpdateProjectInvalidData(): \Generator
    {
        yield 'Without title' => [
            null,
            'links'   => [
                ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
            ],
            'locales' => ['en'],
        ];

        yield 'With blank title' => [
            '',
            'links'   => [
                ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
            ],
            'locales' => ['en'],
        ];

        yield 'Without locales' => [
            'title' => 'Test',
            'links' => [
                ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
            ],
            null,
        ];

        yield 'With empty locale list' => [
            'title' => 'Test',
            'links' => [
                ['title' => 'github', 'url' => 'https://github.com/Sonny812/ub-tracker-api'],
            ],
            [],
        ];

        yield 'With invalid url in link' => [
            'Test',
            'links'   => [
                ['title' => 'github', 'url' => 'not a url'],
            ],
            'locales' => ['en'],
        ];
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
            ->setMaxResults('1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \App\Repository\ProjectRepository
     * '*/
    private function getProjectRepository(): ProjectRepository
    {
        return self::$container->get('doctrine.orm.default_entity_manager')->getRepository(Project::class);
    }
}
