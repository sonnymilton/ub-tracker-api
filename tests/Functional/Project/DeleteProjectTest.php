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
 * Delete project test
 */
class DeleteProjectTest extends AbstractApiTest
{
    public function testDeleteProject(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['admin']);

        $client->request(Request::METHOD_DELETE, sprintf('/api/project/%d/', $this->getExistingProjectId()));
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testNotAdminCantDeleteProject(): void
    {
        $this->expectException(AccessDeniedException::class);

        $client = self::$client;
        $client->catchExceptions(false);
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['qa']);

        $client->request(Request::METHOD_DELETE, sprintf('/api/project/%d/', $this->getExistingProjectId()));
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testDeleteNotExistingProject(): void
    {
        $client = self::$client;
        $client->setServerParameter(self::AUTH_PARAMETER_NAME, self::$roleTokenMap['admin']);

        $client->request(Request::METHOD_DELETE, sprintf('/api/project/%d/', 1230901230939));
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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
