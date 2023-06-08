<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Factory\AvatarFactory;
use App\Factory\CommentFactory;
use App\Factory\GroupFactory;
use App\Factory\TrickFactory;
use App\Factory\TrickImageFactory;
use App\Factory\UserFactory;
use App\Factory\VideoFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create 80 users

        UserFactory::new()->createMany(80, function () {
            return [
                'avatar' => AvatarFactory::new()->create(),
            ];
        });

        // Create 10 Groups
        GroupFactory::new()->createMany(10);

        // Create tricks
        $tricks = TrickFactory::new()->createMany(10, function () {
            return [
                'images' => TrickImageFactory::new()->createMany(rand(1, 5)),
            ];
        });

        // for each trick, create 3 to 15 comments
        foreach ($tricks as $trick) {
            VideoFactory::new()->createMany(rand(1, 4));
            CommentFactory::new()->createMany(rand(3, 15), function () use ($trick) {
                return [
                    'trick' => $trick,
                    'author' => UserFactory::random(),
                ];
            });
        }
    }
}
