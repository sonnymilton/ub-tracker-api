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
use App\Entity\Security\ApiUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Bug
 *
 * @ORM\Entity(repositoryClass="App\Repository\BugRepository")
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class Bug
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_details"})
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="BugStatusType")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="BugPriorityType")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $priority;

    /**
     * @var Tracker
     *
     * @ORM\ManyToOne(targetEntity="Tracker", inversedBy="bugs")
     *
     */
    protected $tracker;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_details"})
     */
    protected $responsiblePerson;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_details"})
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $createdAt;

    /**
     * Bug constructor.
     *
     * @param ApiUser $author
     * @param Tracker $tracker
     * @param ApiUser $responsiblePerson
     * @param string  $title
     * @param string  $description
     * @param string  $priority
     *
     * @throws \Exception
     */
    public function __construct(ApiUser $author, Tracker $tracker, ApiUser $responsiblePerson, string $title, string $priority, string $description)
    {
        $this->author            = $author;
        $this->responsiblePerson = $responsiblePerson;
        $this->tracker           = $tracker;
        $this->title             = $title;
        $this->description       = $description;
        $this->priority          = $priority;
        $this->status            = BugStatusType::NEW;
        $this->createdAt         = new \DateTimeImmutable();
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
