<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher;
    private \Faker\Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $faker = Factory::create('fr_FR');
        $this->faker = $faker;
    }

    public function load(ObjectManager $manager)
    {
        $filesystem = new Filesystem();
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../../public/assets/default');
        foreach ($finder as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $randomUUID = $this->faker->uuid();
            $relativePath = str_replace('default-avatar.png', $randomUUID.'.png', $relativePath);
            $filesystem->copy($file->getRealPath(), __DIR__.'/../../public/assets/uploads/users/avatars/'.$relativePath);
        }
        for ($i = 0; $i < 25; ++$i) {
            $image = new Image();
            $image->setPath($relativePath);
            $user = new User();
            $user->setUsername($this->faker->userName());
            $user->setEmail($this->faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setAvatar($image);
            $user->setIsVerified(true);

            $manager->persist($user);
            $manager->persist($image);
            $this->addReference('user-'.$i, $user);
        }

        $manager->flush();
    }
}
