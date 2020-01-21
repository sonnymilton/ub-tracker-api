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
 * Reopen voter
 */
class ReopenReportVoter extends AbstractBugReportVoter
{
    /**
     * @var \Symfony\Component\Security\Core\Security
     */
    protected $security;

    /**
     * ReopenVoter constructor.
     *
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    public function supports($attribute, $subject)
    {
        return parent::supports($attribute, $subject) && 'reopen' === $attribute;
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
        return !$subject->isActive() && $this->security->isGranted('ROLE_QA');
    }
}
