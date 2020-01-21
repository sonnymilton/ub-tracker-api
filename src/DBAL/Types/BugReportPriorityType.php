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
 * Class BugReportPriorityType
 */
final class BugReportPriorityType extends AbstractEnumType
{
    public const CRITICAL = 'critical';
    public const MAJOR    = 'major';
    public const MINOR    = 'minor';
    public const NORMAL   = 'normal';

    protected static $choices = [
        self::CRITICAL => 'bugreport_priority.critical',
        self::MAJOR    => 'bugreport_priority.major',
        self::MINOR    => 'bugreport_priority.minor',
        self::NORMAL   => 'bugreport_priority.normal',
    ];
}
