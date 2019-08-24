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

/**
 * Class ApiToken
 *
 * @ORM\Embeddable()
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class ApiToken
{
    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @JMS\Type("string")
     * @JMS\Expose()
     * @JMS\Groups(groups={"user_auth"})
     *
     */
    protected $value;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(type="date_immutable", nullable=true)
     *
     * @JMS\Type("DateTimeImmutable")
     * @JMS\Expose()
     * @JMS\Groups("user_auth")
     */
    protected $expiresAt;

    /**
     * ApiToken constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->value     = bin2hex(openssl_random_pseudo_bytes(32));
        $this->expiresAt = new \DateTimeImmutable('tomorrow');
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
