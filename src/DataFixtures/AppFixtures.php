<?php

namespace App\DataFixtures;

use App\DBAL\Types\BugPriorityType;
use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AppFixtures
 */
class AppFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        /** @var ApiUser $qaUser */
        $qaUser = $this->getReference(UserFixtures::QA_USER_REFERENCE);
        $qaUser->createToken();

        $project = $qaUser->createProject('ub-tracker');
        $tracker = $qaUser->createTracker($project);

        /** @var ApiUser[] $developers */
        $developers = [
            $this->getReference(UserFixtures::FIRST_DEVELOPER_REFERENCE),
            $this->getReference(UserFixtures::SECOND_DEVELOPER_REFERENCE)
        ];

        foreach ($developers as $developer) {
            $developer->createToken();
            $project->addDeveloper($developer);
        }


        for ($i=0; $i<=20; $i++) {
            $qaUser->createBug(
                $developers[mt_rand(0, 1)],
                $tracker,
                sprintf('bug %d', $i),
                sprintf('bug %d description', $i),
                BugPriorityType::getRandomValue()
            );
        }

        $manager->persist($project);

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
