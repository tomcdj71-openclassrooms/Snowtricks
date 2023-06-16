<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Group;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private SluggerInterface $slugger;

    public function __construct(UserPasswordHasherInterface $hasher, SluggerInterface $slugger)
    {
        $this->hasher = $hasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();
        // Create 20 users each with an avatar
        $users = [];
        for ($i = 0; $i < 20; ++$i) {
            $user = new User();
            $user->setUsername($faker->userName);
            $user->setEmail($faker->email);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setIsVerified(true);
            $manager->persist($user);
            $avatar = new Image();
            $avatar->setPath('default-avatar-'.$i.'.jpg');
            $avatar->setUser($user);
            $manager->persist($avatar);
            $users[] = $user;
        }
        // Create 5 groups
        $groups = [];
        for ($i = 0; $i < 5; ++$i) {
            $group = new Group();
            $group->setName($faker->word);
            $manager->persist($group);
            $groups[] = $group;
        }
        // Create 30 tricks
        for ($i = 0; $i < 30; ++$i) {
            $trickAuthor = $faker->randomElement($users);
            $title = $faker->sentence;
            $trick = new Trick();
            $trick->setTitle($title);
            $trick->setDescription($faker->text);
            $trick->setCreatedAt(new \DateTimeImmutable());
            $trick->setSlug($this->slugger->slug($title));
            $trick->setGroup($faker->randomElement($groups));
            $trick->setAuthor($trickAuthor);
            $manager->persist($trick);
            // Each trick has 1 featured image
            $featuredImage = new Image();
            $featuredImage->setPath('default-featured.'.$i.'.jpg');
            $featuredImage->setTrick($trick);
            $manager->persist($featuredImage);
            $trick->setFeaturedImage($featuredImage);
            // Each trick has 2 to 5 images
            for ($j = 0; $j < rand(2, 5); ++$j) {
                $image = new Image();
                $image->setPath('default-images-'.$i.'-'.$faker->randomDigitNotNull.'.jpg');
                $image->setTrick($trick);
                $manager->persist($image);
            }
            // Each trick has 0 to 2 videos
            for ($j = 0; $j < rand(0, 2); ++$j) {
                $video = new Video();
                $video->setPath('https://youtube.com-'.$i.'-'.$faker->randomDigitNotNull);
                $video->setTrick($trick);
                $manager->persist($video);
            }
            // Each trick has 2 to 10 comments
            for ($j = 0; $j < rand(2, 10); ++$j) {
                $comment = new Comment();
                $comment->setContent($faker->sentence);
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setAuthor($faker->randomElement($users));
                $comment->setTrick($trick);
                $manager->persist($comment);
            }
        }
        $manager->flush();
    }
}
