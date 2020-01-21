<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Voter\BugReport;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Responsible person voter
 */
class ResponsiblePersonReportVoter extends AbstractBugReportVoter
{
    const CANT_BE_REPRODUCED = 'cant_be_reproduced';
    const SEND_TO_VERIFY     = 'send_to_verify';

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        return parent::supports($attribute, $subject) && in_array($attribute, [
                self::CANT_BE_REPRODUCED,
                self::SEND_TO_VERIFY,
            ]);
    }

    /**
     * @param string                                                               $attribute
     * @param \App\Entity\BugReport\BugReport                                      $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return bool|void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var \App\Entity\Security\ApiUser $user */
        $user = $token->getUser();

        return $subject->getResponsiblePerson() === $user && $subject->isActive();
    }
}
