<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Response\Security;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AuthMethodResponseObject
 *
 * @JMS\ExclusionPolicy("NONE")
 */
class AuthMethodResponseObject
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $url;

    /**
     * AuthMethodResponseObject constructor.
     *
     * @param string $name
     * @param string $url
     */
    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url  = $url;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
