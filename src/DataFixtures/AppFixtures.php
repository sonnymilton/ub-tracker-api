<?php

namespace App\DataFixtures;

use App\DBAL\Types\BugPriorityType;
use App\Entity\Bug;
use App\Entity\Project;
use App\Entity\Security\ApiUser;
use App\Entity\Tracker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $project = new Project('ub-tracker');
        $tracker = new Tracker($project);
        $project->addTracker($tracker);
        for ($i=0; $i<=20; $i++) {
            $tracker->addBug(new Bug(sprintf('bug %d', $i), $tracker, sprintf('bug %d descipriton', $i),
                BugPriorityType::getRandomValue(), null));

        }
        $manager->persist($project);

        $user = new ApiUser('admin', 'admin@example.com', 'admin', ['ROLE_ADMIN']);
        $user->createToken();
        $manager->persist($user);

        $manager->flush();
    }
}
