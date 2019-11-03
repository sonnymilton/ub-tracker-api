<?php

namespace App\DataFixtures;

use App\DBAL\Types\BugPriorityType;
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

        $project = $qaUser->createProject('ub-tracker', ['ru', 'en'], [
            'task'       => 'https://task-manager.example.com/task/1/',
            'repository' => 'https://github.com/Sonny812/ub-tracker-api',
            'liveSite'   => 'https://ub-tracker.example.com/',
            'testSite'   => 'https://stage.ub-tracker.example.com/',
        ]);
        $tracker = $qaUser->createTracker($project);

        /** @var ApiUser[] $developers */
        $developers = [
            $this->getReference(UserFixtures::FIRST_DEVELOPER_REFERENCE),
            $this->getReference(UserFixtures::SECOND_DEVELOPER_REFERENCE),
        ];

        foreach ($developers as $developer) {
            $developer->createToken();
            $project->addDeveloper($developer);
        }

        for ($i = 0; $i <= 20; $i++) {
            $this->generateBug($qaUser, $developers, $tracker, $i);
        }

        $manager->persist($project);

        $manager->flush();
    }

    /**
     * @param \App\Entity\Security\ApiUser         $qa
     * @param array|\App\Entity\Security\ApiUser[] $developers
     * @param \App\Entity\Tracker                  $tracker
     * @param int                                  $number
     *
     * @return \App\Entity\Bug
     *
     * @throws \Exception
     */
    protected function generateBug(ApiUser $qa, array $developers, Tracker $tracker, int $number)
    {
        $localesCases = [
            ['ru'],
            ['en'],
            ['ru', 'en'],
            [],
        ];

        $resolutionCases = [
            ['320x480'],
            ['320x480', '480x720'],
            ['1080x1920'],
            [],
            [],
        ];

        $browserCases = [
            ['firefox'],
            ['chrome'],
            ['safari'],
            ['firefox', 'safari'],
            [],
            [],
        ];

        return $qa->createBug(
            $developers[mt_rand(0, 1)],
            $tracker,
            sprintf('bug %d', $number),
            BugPriorityType::getRandomValue(),
            sprintf('bug %d description', $number),
            $browserCases[mt_rand(0, 5)],
            $resolutionCases[mt_rand(0, 4)],
            $localesCases[mt_rand(0, 3)]
        );
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
