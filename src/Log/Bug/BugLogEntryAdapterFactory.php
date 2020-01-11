<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Log\Bug;

use App\Entity\Security\ApiUser;
use App\Repository\Security\ApiUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;

/**
 * Bug log entry adapter factory
 */
final class BugLogEntryAdapterFactory
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * BugLogEntryAdapterFactory constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param \Gedmo\Loggable\Entity\LogEntry $logEntry
     *
     * @return \App\Log\Bug\BugLogEntryAdapter
     */
    public function createAdapter(LogEntry $logEntry): BugLogEntryAdapter
    {
        return new BugLogEntryAdapter(
            $logEntry->getAction(),
            $logEntry->getLoggedAt(),
            $logEntry->getObjectId(),
            $logEntry->getVersion(),
            $this->resolveDataEntities($logEntry->getData()),
            $this->resolveUser($logEntry->getUsername())
        );
    }

    /**
     * @param array|LogEntry[] $logEntries
     *
     * @return array|\App\Log\Bug\BugLogEntryAdapter[]
     */
    public function createAdapters(array $logEntries): array
    {
        $users = $this->getUserRepository()->findAll();

        $usersWithKeys = array_reduce($users, function ($result, ApiUser $user) {
            $result['by_username'][$user->getUsername()] = $user;
            $result['by_id'][$user->getId()]             = $user;

            return $result;
        });

        $usersByUsernames = &$usersWithKeys['by_username'];
        $usersByIds       = &$usersWithKeys['by_id'];

        $resolveData = function (array $data) use ($usersByIds): array {
            if (isset($data['responsiblePerson'])) {
                $data['responsiblePerson'] = $usersByIds[$data['responsiblePerson']['id']];
            }

            return $data;
        };

        return array_map(function (LogEntry $logEntry) use ($usersByUsernames, $resolveData): BugLogEntryAdapter {
            return new BugLogEntryAdapter(
                $logEntry->getAction(),
                $logEntry->getLoggedAt(),
                $logEntry->getObjectId(),
                $logEntry->getVersion(),
                $resolveData($logEntry->getData()),
                $usersByUsernames[$logEntry->getUsername()] ?? $logEntry->getUsername()
            );
        }, $logEntries);
    }

    /**
     * @param string $username
     *
     * @return object|string
     */
    private function resolveUser(string $username)
    {
        return $this->getUserRepository()->findOneBy(['username' => $username]) ?? $username;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function resolveDataEntities(array $data): array
    {
        if (isset($data['responsiblePerson'])) {
            $data['responsiblePerson'] = $this->getUserRepository()->find($data['responsiblePerson']['id']);
        }

        return $data;
    }

    /**
     * @return \App\Repository\Security\ApiUserRepository
     */
    private function getUserRepository(): ApiUserRepository
    {
        return $this->em->getRepository(ApiUser::class);
    }
}
