<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Voter\Comment;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Comment voter
 */
class CommentVoter extends Voter
{
    const EDIT   = 'edit';
    const DELETE = 'delete';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool|void
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Comment && in_array($attribute, [self::EDIT, self::DELETE]);
    }

    /**
     * @param string                                                               $attribute
     * @param Comment                                                              $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return bool|void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $subject->getAuthor() === $token->getUser();
    }
}
