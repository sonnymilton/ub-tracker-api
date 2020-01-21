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
 * Class BugReportStatusType
 */
final class BugReportStatusType extends AbstractEnumType
{
    public const CANT_REPRODUCE  = 'cant_reproduce';
    public const CLOSED          = 'closed';
    public const NEW             = 'new';
    public const TO_BE_DISCUSSED = 'to_be_discussed';
    public const TO_VERIFY       = 'to_verify';
    public const RETURNED        = 'returned';
    public const VERIFIED        = 'verified';

    protected static $choices = [
        self::CANT_REPRODUCE  => 'bugreport_status.cant_reproduce',
        self::CLOSED          => 'bugreport_status.closed',
        self::NEW             => 'bugreport_status.new',
        self::TO_BE_DISCUSSED => 'bugreport_status.to_be_discussed',
        self::TO_VERIFY       => 'bugreport_status.to_verify',
        self::RETURNED        => 'bugreport_status.returned',
        self::VERIFIED        => 'bugreport_status.verified',
    ];
}
