<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $groups = ['Rotations désaxées', 'Rotations', 'Flips', 'Grabs', 'Switch'];

        foreach ($groups as $groupName) {
            $group = new Group();
            $group->setName($groupName);
            $manager->persist($group);
            $this->addReference($groupName, $group);
        }

        $manager->flush();
    }
}
