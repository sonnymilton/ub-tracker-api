<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Voter\Bug;

use App\Entity\Bug;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Abstract bug voter
 */
abstract class AbstractBugVoter extends Voter
{
    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof Bug) {
            return false;
        }
    }
}
