<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Resolvable interface
 */
interface ResolvableInterface
{
    /**
     * @return bool
     */
    function isResolved(): bool;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager
     */
    function resolve(ObjectManager $entityManager): void;
}
