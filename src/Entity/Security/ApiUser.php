<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Security;

use App\Entity\BugReport\BugReport;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\Tracker;
use App\Request\BugReport\CreateBugReportRequest;
use App\Request\Comment\CommentRequest;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ApiUser
 *
 * @ORM\Entity(repositoryClass="App\Repository\Security\ApiUserRepository")
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class ApiUser implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_details", "user_list"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true, length=50)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_details", "user_list"})
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(unique=true, length=50)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_details"})
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $password;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $roles;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $salt;

    /**
     * @var ApiToken
     *
     * @ORM\Embedded(class="App\Entity\Security\ApiToken")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_auth"})
     * @JMS\Type(ApiToken::class)
     *
     * @SWG\Property(ref="#/definitions/Token")
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $code;

    /**
     * ApiUser constructor.
     *
     * @param string     $username
     * @param string     $email
     * @param string     $password
     * @param array|null $roles
     */
    public function __construct(string $username, string $email, string $password, array $roles = null)
    {
        $this->username = $username;
        $this->email    = $email;
        $this->password = $password;
        $this->roles    = $roles;
        $this->salt     = hash('sha512', uniqid((string)mt_rand(), true));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     *
     * @JMS\VirtualProperty(name="roles")
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_details"})
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles [] = 'ROLE_USER';

        return $roles;
    }

    /**
     * @return bool
     */
    public function isDeveloper(): bool
    {
        return in_array('ROLE_DEVELOPER', $this->roles);
    }

    /**
     * @return bool
     */
    public function isQA(): bool
    {
        return in_array('ROLE_QA', $this->roles);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * @throws \Exception
     */
    public function createToken(): void
    {
        $this->token = new ApiToken();
        $this->code  = null;
    }

    /**
     * @return ApiToken|null
     */
    public function getToken(): ?ApiToken
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        // do nothing
        return;
    }

    /**
     * @param string $token
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validateToken(string $token): bool
    {
        return $this->token->getExpiresAt() >= new DateTimeImmutable() && $token == $this->token->getValue();
    }

    /**
     * @param string $code
     */
    public function updateCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string         $title
     * @param array|string[] $locales
     * @param array|null     $links
     *
     * @return Project
     *
     * @throws \Exception
     */
    public function createProject(string $title, array $locales, array $links = null): Project
    {
        return new Project($this, $title, $locales, $links);
    }

    /**
     * @param Project $project
     * @param array   $developers
     * @param array   $links
     *
     * @return Tracker
     *
     * @throws \Exception
     */
    public function createTracker(Project $project, array $developers = [], array $links = []): Tracker
    {
        $tracker = new Tracker($this, $project, $developers, $links);
        $project->addTracker($tracker);

        return $tracker;
    }

    /**
     * @param ApiUser    $responsiblePerson
     * @param Tracker    $tracker
     * @param string     $title
     * @param string     $priority
     * @param string     $description
     * @param array|null $browsers
     * @param array|null $resolutions
     * @param array|null $locales
     *
     * @return BugReport
     *
     * @throws \Exception
     */
    public function createBugReport(
        ApiUser $responsiblePerson,
        Tracker $tracker,
        string $title,
        string $priority,
        string $description,
        array $browsers = null,
        array $resolutions = null,
        array $locales = null
    ): BugReport {
        $bugReport = new BugReport(
            $this,
            $tracker,
            $responsiblePerson,
            $title,
            $priority,
            $description,
            $browsers,
            $resolutions,
            $locales
        );
        $tracker->addBugReport($bugReport);

        return $bugReport;
    }

    /**
     * @param \App\Request\BugReport\CreateBugReportRequest $request
     * @param \App\Entity\Tracker                           $tracker
     * @param \App\Entity\Security\ApiUser                  $responsiblePerson
     *
     * @return \App\Entity\BugReport\BugReport
     *
     * @throws \Exception
     */
    public function createBugReportFromRequest(CreateBugReportRequest $request, Tracker $tracker, ApiUser $responsiblePerson): BugReport
    {
        return $this->createBugReport(
            $responsiblePerson,
            $tracker,
            $request->getTitle(),
            $request->getPriority(),
            $request->getDescription(),
            $request->getBrowsers(),
            $request->getResolutions(),
            $request->getLocales()
        );
    }

    /**
     * @param \App\Entity\BugReport\BugReport $bugReport
     * @param string                          $text
     *
     * @return \App\Entity\Comment
     *
     * @throws \Exception
     */
    public function createComment(BugReport $bugReport, string $text): Comment
    {
        $comment = new Comment($this, $bugReport, $text);
        $bugReport->addComment($comment);

        return $comment;
    }

    /**
     * @param \App\Request\Comment\CommentRequest $request
     * @param \App\Entity\BugReport\BugReport     $bugReport
     *
     * @return \App\Entity\Comment
     *
     * @throws \Exception
     */
    public function createCommentFromRequest(CommentRequest $request, BugReport $bugReport): Comment
    {
        return $this->createComment($bugReport, $request->getText());
    }
}
