<?php

namespace App\DataFixtures;

use App\DBAL\Types\BugPriorityType;
use App\Entity\Bug;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class AppFixtures
 */
class AppFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * AppFixtures constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create('en');
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        /** @var ApiUser $adminUser */
        $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE);
        $adminUser->createToken();

        /** @var ApiUser $qaUser */
        $qaUser = $this->getReference(UserFixtures::QA_USER_REFERENCE);
        $qaUser->createToken();

        $project = $qaUser->createProject('ub-tracker', ['ru', 'en'], [
            ['title' => 'task', 'url' => 'https://task-manager.example.com/task/1/',],
            ['title' => 'repository', 'url' => 'https://github.com/Sonny812/ub-tracker-api',],
            ['title' => 'liveSite', 'url' => 'https://ub-tracker.example.com/',],
            ['title' => 'testSite', 'url' => 'https://stage.ub-tracker.example.com/',],
        ]);
        $tracker = $qaUser->createTracker($project);

        /** @var ApiUser[] $developers */
        $developers = [
            $this->getReference(UserFixtures::FIRST_DEVELOPER_REFERENCE),
            $this->getReference(UserFixtures::SECOND_DEVELOPER_REFERENCE),
        ];

        $users = array_merge($developers, [$qaUser, $this->getReference(UserFixtures::ADMIN_USER_REFERENCE)]);

        foreach ($developers as $developer) {
            $developer->createToken();
            $tracker->addDeveloper($developer);
        }

        for ($i = 0; $i <= 20; $i++) {
            $bug = $this->generateBug($qaUser, $developers, $tracker);

            for ($j = 0; $j <= mt_rand(0, 10); $j++) {
                /** @var ApiUser $user */
                $user = $this->faker->randomElement($users);

                $user->createComment($bug, $this->faker->realText(mt_rand(20, 200)));
            }
        }

        $manager->persist($project);

        $manager->flush();
    }

    /**
     * @param \App\Entity\Security\ApiUser         $qa
     * @param array|\App\Entity\Security\ApiUser[] $developers
     * @param \App\Entity\Tracker                  $tracker
     *
     * @return \App\Entity\Bug
     *
     * @throws \Exception
     */
    protected function generateBug(ApiUser $qa, array $developers, Tracker $tracker): Bug
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
            $this->faker->randomElement($developers),
            $tracker,
            $this->faker->title,
            BugPriorityType::getRandomValue(),
            $this->faker->realText(mt_rand(10, 200)),
            $this->faker->randomElement($browserCases),
            $this->faker->randomElement($resolutionCases),
            $this->faker->randomElement($localesCases)
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
