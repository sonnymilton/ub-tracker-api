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

use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Project\Links;
use App\Entity\Tracker;
use App\Request\Bug\CreateBugRequest;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
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
        return $this->token->getExpiresAt() >= new \DateTimeImmutable() && $token == $this->token->getValue();
    }

    /**
     * @param string $code
     */
    public function updateCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string                    $title
     * @param array|string[]            $locales
     * @param \App\Entity\Project\Links $links
     *
     * @return Project
     *
     * @throws \Exception
     */
    public function createProject(string $title, array $locales, Links $links = null): Project
    {
        return new Project($this, $title, $locales, $links);
    }

    /**
     * @param Project $project
     *
     * @return Tracker
     *
     * @throws \Exception
     */
    public function createTracker(Project $project): Tracker
    {
        $tracker = new Tracker($this, $project);
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
     * @return Bug
     *
     * @throws \Exception
     */
    public function createBug(
        ApiUser $responsiblePerson,
        Tracker $tracker,
        string $title,
        string $priority,
        string $description,
        array $browsers = null,
        array $resolutions = null,
        array $locales = null
    ): Bug {
        $bug = new Bug(
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
        $tracker->addBug($bug);

        return $bug;
    }

    /**
     * @param \App\Request\Bug\CreateBugRequest $request
     * @param \App\Entity\Tracker               $tracker
     * @param \App\Entity\Security\ApiUser      $responsiblePerson
     *
     * @return \App\Entity\Bug
     *
     * @throws \Exception
     */
    public function createBugFromRequest(CreateBugRequest $request, Tracker $tracker, ApiUser $responsiblePerson): Bug
    {
        return $this->createBug(
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
}
