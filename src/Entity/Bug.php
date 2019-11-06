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
use App\Request\Bug\UpdateBugRequest;
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
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
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
     */
    protected $tracker;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $responsiblePerson;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
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
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $browsers;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $resolutions;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bug_list", "bug_details"})
     */
    protected $locales;

    /**
     * Bug constructor.
     *
     * @param ApiUser    $author
     * @param Tracker    $tracker
     * @param ApiUser    $responsiblePerson
     * @param string     $description
     * @param string     $priority
     * @param array|null $browsers
     * @param array|null $resolutions
     * @param array|null $locales
     *
     * @throws \Exception
     */
    public function __construct(
        ApiUser $author,
        Tracker $tracker,
        ApiUser $responsiblePerson,
        string $description,
        string $priority,
        array $browsers = null,
        array $resolutions = null,
        array $locales = null
    ) {
        $this->author            = $author;
        $this->responsiblePerson = $responsiblePerson;
        $this->tracker           = $tracker;
        $this->description       = $description;
        $this->priority          = $priority;
        $this->browsers          = $browsers;
        $this->resolutions       = $resolutions;
        $this->locales           = $locales;
        $this->status            = BugStatusType::NEW;
        $this->createdAt         = new \DateTimeImmutable();
    }

    /**
     * @param \App\Request\Bug\UpdateBugRequest $request
     * @param \App\Entity\Security\ApiUser      $responsiblePerson
     */
    public function updateFromRequest(UpdateBugRequest $request, ApiUser $responsiblePerson)
    {
        $this->description       = $request->getDescription();
        $this->priority          = $request->getPriority();
        $this->responsiblePerson = $responsiblePerson;
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
     * @return \App\Entity\Tracker
     */
    public function getTracker(): Tracker
    {
        return $this->tracker;
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
     * @return \App\Entity\Security\ApiUser
     */
    public function getAuthor(): ApiUser
    {
        return $this->author;
    }

    /**
     * @return array|null
     */
    public function getBrowsers(): ?array
    {
        return $this->browsers;
    }

    /**
     * @return array|null
     */
    public function getResolutions(): ?array
    {
        return $this->resolutions;
    }

    /**
     * @return array|null
     */
    public function getLocales(): ?array
    {
        return $this->locales;
    }

    /**
     * @param string $description
     */
    public function changeDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return BugStatusType::VERIFIED !== $this->status && BugStatusType::CLOSED !== $this->status;
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

    public function reopen(): void
    {
        $this->status = BugStatusType::NEW;
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
