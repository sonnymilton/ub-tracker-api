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
 * Class BugPriorityType
 */
final class BugPriorityType extends AbstractEnumType
{
    public const CRITICAL = 'critical';
    public const MAJOR    = 'major';
    public const MINOR    = 'minor';
    public const NORMAL   = 'normal';

    protected static $choices = [
        self::CRITICAL => 'bug_priority.critical',
        self::MAJOR    => 'bug_priority.major',
        self::MINOR    => 'bug_priority.minor',
        self::NORMAL   => 'bug_priority.normal',
    ];
}
