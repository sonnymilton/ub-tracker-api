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

use App\DBAL\Types\BugReportStatusType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Verify voter
 */
class VerifyReportVoter extends AbstractBugReportVoter
{
    /**
     * @var \Symfony\Component\Security\Core\Security
     */
    protected $security;

    /**
     * VerifyVoter constructor.
     *
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @inheritDoc
     */
    public function supports($attribute, $subject)
    {
        return parent::supports($attribute, $subject) && 'verify' === $attribute;
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
        return $subject->getStatus() === BugReportStatusType::TO_VERIFY && $this->security->isGranted('ROLE_QA');
    }
}
