<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Voter\BugReport;

use App\Entity\BugReport\BugReport;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Undo change status voter
 */
class UndoChangeStatusVoter extends Voter
{
    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        return $attribute === 'undo' && $subject instanceof LogEntry && $subject->getObjectClass() === BugReport::class;
    }

    /**
     * @param string                                                               $attribute
     * @param LogEntry                                                             $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return bool|string
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $data = $subject->getData();

        return $token->getUsername() === $subject->getUsername() && array_key_exists('status', $data);
    }
}
