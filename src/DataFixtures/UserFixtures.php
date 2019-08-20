<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\DataFixtures;


use App\Entity\Security\ApiUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserFixtures
 */
class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE       = 'admin_user';
    public const QA_USER_REFERENCE          = 'qa_user';
    public const FIRST_DEVELOPER_REFERENCE  = 'developer1_user';
    public const SECOND_DEVELOPER_REFERENCE = 'developer2_user';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $admin = new ApiUser('admin', 'admin@example.com', 'admin', ['ROLE_ADMIN']);
        $qa = new ApiUser('qa', 'qa@example.com', 'qa', ['ROLE_QA']);
        $developer1 = new ApiUser('developer1', 'developer1@example.com', 'dev1', ['ROLE_DEVELOPER']);
        $developer2 = new ApiUser('developer2', 'developer2@example.com', 'dev2', ['ROLE_DEVELOPER']);

        foreach ([$admin, $qa, $developer1, $developer2] as $user) {
            $manager->persist($user);
        }

        $manager->flush();

        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);
        $this->addReference(self::QA_USER_REFERENCE, $qa);
        $this->addReference(self::FIRST_DEVELOPER_REFERENCE, $developer1);
        $this->addReference(self::SECOND_DEVELOPER_REFERENCE, $developer2);
    }
}
