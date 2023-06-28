<?php

namespace App\DataFixtures;

use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VideoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $videoData = [
            ['https://www.youtube.com/watch?v=3XxfClLqjg4', 'https://www.youtube.com/watch?v=qeSCR8ClxnQ'],
            ['https://www.dailymotion.com/video/x49jqw', 'https://www.youtube.com/watch?v=QX6yvs6uTVg', 'https://www.youtube.com/watch?v=vf9Z05XY79A'],
            ['https://www.youtube.com/watch?v=C38Z8gQ02zM', 'https://www.youtube.com/watch?v=dKNqo1N0O_8'],
            ['https://www.dailymotion.com/video/x6sg292', 'https://player.vimeo.com/video/111355717', 'https://www.youtube.com/watch?v=M1KqNOYjxls'],
            ['https://player.vimeo.com/video/107453358', 'https://www.dailymotion.com/video/xxxu60', 'https://www.dailymotion.com/video/xwx80n'],
            ['https://www.youtube.com/watch?v=R5FulIAk4aU', 'https://www.youtube.com/watch?v=z_PoEGLQKio'],
            ['https://www.dailymotion.com/video/x5bsw8', 'https://www.youtube.com/watch?v=GOVorL7Vdzg'],
            ['https://www.youtube.com/watch?v=Vl6Cw4DLbWg', 'https://player.vimeo.com/video/257869431'],
            ['https://www.dailymotion.com/video/x2ftodt', 'https://www.dailymotion.com/video/x1vy1u7', 'https://www.youtube.com/watch?v=Br6ZJM01I6s'],
            ['https://www.dailymotion.com/video/x6eaiij', 'https://www.youtube.com/watch?v=f9FjhCt_w2U', 'https://www.youtube.com/watch?v=xXCCGYqAWqI'],
        ];

        foreach ($videoData as $index => $videos) {
            foreach ($videos as $url) {
                $video = new Video();
                $video->setPath($url);
                $video->setTrick($this->getReference('trick-'.$index));

                $manager->persist($video);
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
