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
     * @JMS\Groups(groups={"user_details"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_details", "user_list"})
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
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
     *
     * @JMS\Groups(groups={"user_details"})
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
     * @param string $username
     * @param string $email
     * @param string $password
     * @param array|null $roles
     */
    public function __construct(string $username, string $email, string $password, array $roles = null)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->salt = hash('sha512', uniqid((string)mt_rand(), true));
    }


    /**
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles []= 'ROLE_USER';

        return $roles;
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
        $this->code = null;
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
}
