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

use App\DBAL\Types\BugStatusType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Return voter
 */
class ReturnVoter extends AbstractBugVoter
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
     * @param string $attribute
     * @param \App\Entity\Bug  $subject
     *
     * @return bool
     */
    public function supports($attribute, $subject)
    {
        return parent::supports($attribute, $subject) &&
            $attribute === 'return';
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $subject->getStatus() === BugStatusType::TO_VERIFY && $this->security->isGranted('ROLE_QA');
    }
}
