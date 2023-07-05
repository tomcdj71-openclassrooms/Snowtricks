<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        $tricksData = [
            ['title' => '1080°', 'group' => 'Rotations', 'description' => 'Trois tours horizontaux complets (1080 degrés) pendant le saut.'],
            ['title' => 'Backside Rodeo', 'group' => 'Rotations désaxées', 'description' => 'Un backflip couplé à une rotation backside, impliquant un flip vertical et une rotation horizontale.'],
            ['title' => 'McTwist', 'group' => 'Flips', 'description' => 'Un flip frontside 540 (un tour et demi) généralement effectué dans un half-pipe.'],
            ['title' => 'Double Backflip', 'group' => 'Flips', 'description' => 'Deux backflips consécutifs en un seul saut, ce qui est risqué et nécessite une grande habileté.'],
            ['title' => 'Backside Triple Cork 1440', 'group' => 'Rotations désaxées', 'description' => 'Trois flips désaxés (corks) et quatre tours complets (1440 degrés).'],
            ['title' => 'Cork 720', 'group' => 'Rotations désaxées', 'description' => 'Une rotation de deux tours complets (720 degrés) combinée avec une rotation désaxée.'],
            ['title' => 'Cab 900', 'group' => 'Rotations', 'description' => 'Un 900 effectué en position switch (switch frontside 900).'],
            ['title' => 'Switch Backside 540', 'group' => 'Rotations', 'description' => 'Une rotation de 540 degrés effectuée en position switch.'],
            ['title' => 'Triple Cork', 'group' => 'Rotations désaxées', 'description' => 'Trois flips désaxés (corks) et quatre tours complets (1440 degrés).'],
            ['title' => 'Stalefish', 'group' => 'Grabs', 'description' => 'Saisie de la carre backside de la planche, entre les deux pieds, avec la main arrière.'],
        ];

        foreach ($tricksData as $index => $trickData) {
            $author = $this->getReference('user-'.rand(0, 24));
            $trick = new Trick();
            $trick->setTitle($trickData['title']);
            $trick->setDescription($trickData['description']);
            $trick->setAuthor($author);
            $trick->setGroup($this->getReference($trickData['group']));
            $trick->setCreatedAt(new \DateTimeImmutable());
            $trick->setSlug($this->slugger->slug($trick->getTitle())->lower());
            $this->addReference('trick-'.$index, $trick);

            $manager->persist($trick);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GroupFixtures::class,
            UserFixtures::class,
        ];
    }
}
