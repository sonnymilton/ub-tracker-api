<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Entity;

use App\DBAL\Types\BugPriorityType;
use App\DBAL\Types\BugStatusType;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Bug
 *
 * @ORM\Entity(repositoryClass="App\Repository\BugRepository")
 */
class Bug
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="BugStatusType")
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="BugPriorityType")
     */
    protected $priority;

    /**
     * @var Tracker
     *
     * @ORM\ManyToOne(targetEntity="Tracker", inversedBy="bugs")
     */
    protected $tracker;

    /**
     * @todo add user entity
     */
    protected $responsiblePerson;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime_immutable")
     */
    protected $createdAt;

    /**
     * Bug constructor.
     *
     * @param string $title
     * @param Tracker $tracker
     * @param string $description
     * @param string $priority
     * @param $responsiblePerson
     *
     * @throws \Exception
     */
    public function __construct(string $title, Tracker $tracker, string $description, string $priority, $responsiblePerson)
    {
        $this->title = $title;
        $this->tracker = $tracker;
        $this->description = $description;
        $this->priority = $priority;
        $this->responsiblePerson = $responsiblePerson;
        $this->status = BugStatusType::NEW;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * @return mixed
     */
    public function getResponsiblePerson()
    {
        return $this->responsiblePerson;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @param string $title
     */
    public function changeTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $description
     */
    public function changeDescription(string $description): void
    {
        $this->description = $description;
    }

    public function cantBeReproduced(): void
    {
        $this->status = BugStatusType::CANT_REPRODUCE;
    }

    public function close(): void
    {
        $this->status = BugStatusType::CLOSED;
    }

    public function bugReturn(): void
    {
        $this->status = BugStatusType::RETURNED;
    }

    public function verify(): void
    {
        $this->status = BugStatusType::VERIFIED;
    }

    public function sendToVerify(): void
    {
        $this->status = BugStatusType::TO_VERIFY;
    }

    public function sendToDiscuss(): void
    {
        $this->status = BugStatusType::TO_BE_DISCUSSED;
    }

    public function changePriorityToNormal(): void
    {
        $this->priority = BugPriorityType::NORMAL;
    }

    public function changePriorityToCritical(): void
    {
        $this->priority = BugPriorityType::CRITICAL;
    }

    public function changePriorityToMinor(): void
    {
        $this->priority = BugPriorityType::MINOR;
    }

    public function changePriorityToMajor(): void
    {
        $this->priority = BugPriorityType::MAJOR;
    }
}
