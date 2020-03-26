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

use Doctrine\ORM\EntityManagerInterface;

/**
 * Resolvable interface
 */
interface HasResolvableEntitiesInterface
{
    /**
     * @return bool
     */
    function isResolved(): bool;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    function resolve(EntityManagerInterface $entityManager): void;
}
