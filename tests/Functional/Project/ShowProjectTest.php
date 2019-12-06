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

/**
 * Show project test
 */
class ShowProjectTest extends AbstractApiTest
{
    public function testShowProjectList(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(Request::METHOD_GET, '/api/project/');
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());
        $this->assertIsArray(json_decode($response->getContent()));
    }

    public function testShowProject(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(Request::METHOD_GET, sprintf('/api/project/%d/', $this->getExistingProjectId()));
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());
    }

    public function testProjectNotFound()
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['developer']);

        $client->request(Request::METHOD_GET, '/apit/project/200000000000/');
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(), $response->getContent());
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
