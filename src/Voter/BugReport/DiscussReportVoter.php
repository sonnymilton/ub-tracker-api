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
use Symfony\Component\Security\Core\Security;

/**
 * Discuss voter
 */
class DiscussReportVoter extends AbstractBugReportVoter
{
    /**
     * @var \Symfony\Component\Security\Core\Security
     */
    private $security;

    /**
     * ReturnVoter constructor.
     *
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string                          $attribute
     * @param \App\Entity\BugReport\BugReport $subject
     *
     * @return bool
     */
    public function supports($attribute, $subject)
    {
        return parent::supports($attribute, $subject) && $attribute === 'send_to_discuss';
    }

    /**
     * @param string                                                               $attribute
     * @param \App\Entity\BugReport\BugReport                                      $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $subject->isActive() &&
            ($this->security->isGranted('ROLE_QA') || $subject->getResponsiblePerson() === $token->getUser());
    }
}
