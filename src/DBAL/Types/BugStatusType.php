<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * Class BugStatusType
 */
final class BugStatusType extends AbstractEnumType
{
    public const CANT_REPRODUCE     = 'cant_reproduce';
    public const CLOSED             = 'closed';
    public const NEW                = 'new';
    public const TO_BE_DISCUSSED    = 'to_be_discussed';
    public const TO_VERIFY          = 'to_verify';
    public const RETURNED           = 'returned';
    public const VERIFIED           = 'verified';

    protected static $choices = [
        self::CANT_REPRODUCE    => 'bug_status.cant_reproduce',
        self::CLOSED            => 'bug_status.closed',
        self::NEW               => 'bug_status.new',
        self::TO_BE_DISCUSSED   => 'bug_status.to_be_discussed',
        self::TO_VERIFY         => 'bug_status.to_verify',
        self::RETURNED          => 'bug_status.returned',
        self::VERIFIED          => 'bug_status.verified',
    ];
}
