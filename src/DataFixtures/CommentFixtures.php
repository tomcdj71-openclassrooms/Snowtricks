<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private \Faker\Generator $faker;

    public function __construct()
    {
        $faker = Factory::create('fr_FR');
        $this->faker = $faker;
    }

    public function load(ObjectManager $manager)
    {
        $trickRepository = $manager->getRepository(Trick::class);
        $tricks = $trickRepository->findAll();
        foreach ($tricks as $trick) {
            $numComments = rand(5, 15);
            for ($i = 0; $i < $numComments; ++$i) {
                $comment = new Comment();
                $comment->setContent($this->faker->paragraphs(2, true));
                $comment->setCreatedAt(new \DateTimeImmutable());
                $user = $this->getReference('user-'.rand(0, 24));
                $comment->setAuthor($user);
                $comment->setTrick($trick);

                $manager->persist($comment);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}
