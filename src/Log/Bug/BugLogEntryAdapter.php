<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Log\Bug;

use DateTimeInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Bug log entry adapter
 *
 * @JMS\ExclusionPolicy(policy="all")
 */
class BugLogEntryAdapter
{
    /**
     * @var string
     *
     * @JMS\Expose()
     */
    protected $action;

    /**
     * @var \DateTimeInterface
     *
     * @JMS\Expose()
     */
    protected $loggedAt;

    /**
     * @var int
     *
     * @JMS\Expose()
     */
    protected $objectId;

    /**
     * @var int
     *
     * @JMS\Expose()
     */
    protected $version;

    /**
     * @var array
     *
     * @JMS\Expose()
     */
    protected $data;

    /**
     * @var \App\Entity\Security\ApiUser|string
     *
     * @JMS\Expose()
     */
    protected $user;

    /**
     * BugLogEntryAdapter constructor.
     *
     * @param string             $action
     * @param \DateTimeInterface $loggedAt
     * @param int                $objectId
     * @param int                $version
     * @param array              $data
     * @param mixed              $user
     */
    public function __construct(string $action, DateTimeInterface $loggedAt, int $objectId, int $version, array $data, $user)
    {
        $this->action   = $action;
        $this->loggedAt = $loggedAt;
        $this->objectId = $objectId;
        $this->version  = $version;
        $this->data     = $data;
        $this->user     = $user;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLoggedAt(): \DateTimeInterface
    {
        return $this->loggedAt;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return \App\Entity\Security\ApiUser|string
     */
    public function getUser()
    {
        return $this->user;
    }
}
