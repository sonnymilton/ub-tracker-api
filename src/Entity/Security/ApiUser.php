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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ApiUser
 *
 * @ORM\Entity(repositoryClass="App\Repository\Security\ApiUserRepository")
 */
class ApiUser implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
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
     */
    protected $token;

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
    public function createToken()
    {
        $this->token = new ApiToken();
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
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
}
