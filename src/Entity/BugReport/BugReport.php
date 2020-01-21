<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\BugReport;

use App\DBAL\Types\BugReportStatusType;
use App\Entity\Comment;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use App\Request\BugReport\UpdateBugReportRequest;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class BugReport
 *
 * @ORM\Entity(repositoryClass="App\Repository\BugReportRepository")
 *
 * @Gedmo\Loggable
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class BugReport
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_details"})
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="BugReportStatusType")
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(type="BugReportPriorityType")
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $priority;

    /**
     * @var Tracker
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Tracker", inversedBy="bugReports")
     */
    protected $tracker;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     *
     * @SWG\Property(ref="#/definitions/UserFromList")
     */
    protected $responsiblePerson;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     *
     * @SWG\Property(ref="#/definitions/UserFromList")
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $createdAt;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $browsers;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $resolutions;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Gedmo\Versioned()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_list", "bugreport_details"})
     */
    protected $locales;

    /**
     * @var ArrayCollection|\App\Entity\Comment[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="bugReport", cascade={"persist", "remove"})
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"bugreport_details"})
     *
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/Comment"))
     */
    protected $comments;

    /**
     * BugReport constructor.
     *
     * @param ApiUser    $author
     * @param Tracker    $tracker
     * @param ApiUser    $responsiblePerson
     * @param string     $title
     * @param string     $priority
     * @param string     $description
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
        string $title,
        string $priority,
        string $description,
        array $browsers = null,
        array $resolutions = null,
        array $locales = null
    ) {
        $this->author            = $author;
        $this->responsiblePerson = $responsiblePerson;
        $this->tracker           = $tracker;
        $this->title             = $title;
        $this->description       = $description;
        $this->priority          = $priority;
        $this->browsers          = $browsers;
        $this->resolutions       = $resolutions;
        $this->locales           = $locales;
        $this->status            = BugReportStatusType::NEW;
        $this->createdAt         = new DateTimeImmutable();
        $this->comments          = new ArrayCollection();
    }

    /**
     * @param \App\Request\BugReport\UpdateBugReportRequest $request
     * @param \App\Entity\Security\ApiUser                  $responsiblePerson
     */
    public function updateFromRequest(UpdateBugReportRequest $request, ApiUser $responsiblePerson)
    {
        $this->title             = $request->getTitle();
        $this->description       = $request->getDescription();
        $this->priority          = $request->getPriority();
        $this->browsers          = $request->getBrowsers();
        $this->resolutions       = $request->getResolutions();
        $this->locales           = $request->getLocales();
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
     * @return \App\Entity\Comment[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getComments(): ArrayCollection
    {
        return $this->comments;
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

    /**
     * @param \App\Entity\Comment $comment
     */
    public function addComment(Comment $comment): void
    {
        $this->comments->add($comment);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return BugReportStatusType::VERIFIED !== $this->status && BugReportStatusType::CLOSED !== $this->status;
    }

    public function cantBeReproduced(): void
    {
        $this->status = BugReportStatusType::CANT_REPRODUCE;
    }

    public function close(): void
    {
        $this->status = BugReportStatusType::CLOSED;
    }

    public function bugReturn(): void
    {
        $this->status = BugReportStatusType::RETURNED;
    }

    public function verify(): void
    {
        $this->status = BugReportStatusType::VERIFIED;
    }

    public function reopen(): void
    {
        $this->status = BugReportStatusType::NEW;
    }

    public function sendToVerify(): void
    {
        $this->status = BugReportStatusType::TO_VERIFY;
    }

    public function sendToDiscuss(): void
    {
        $this->status = BugReportStatusType::TO_BE_DISCUSSED;
    }
}
