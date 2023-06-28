<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    private $featuredImages = [];

    /**
     * Load images from public/fixtures directory.
     * Images are copied to public/assets/uploads/tricks/images directory.
     * A mini version of the image is created in public/assets/uploads/tricks/images/mini directory.
     */
    public function load(ObjectManager $manager)
    {
        $filesystem = new Filesystem();
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../../public/fixtures');
        $trickRepository = $manager->getRepository(Trick::class);
        $tricks = $trickRepository->findAll();
        $trickIndex = 0;
        foreach ($finder as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $sourcePath = $file->getRealPath();
            $originalPath = __DIR__.'/../../public/assets/uploads/tricks/images/'.$relativePath;
            $miniPath = __DIR__.'/../../public/assets/uploads/tricks/images/mini/'.$relativePath;
            $filesystem->copy($sourcePath, $originalPath);
            $this->resizeImage($sourcePath, $miniPath, 300, 300);
            if ($trickIndex < count($tricks)) {
                $trick = $tricks[$trickIndex];
                ++$trickIndex;
            } else {
                $trickIndex = 0;
                $trick = $tricks[$trickIndex];
            }
            $image = new Image();
            $image->setPath($relativePath);
            $image->setTrick($trick);
            if (!$trick->getFeaturedImage() instanceof Image && !in_array($image->getPath(), $this->featuredImages)) {
                $trick->setFeaturedImage($image);
                $this->featuredImages[] = $image->getPath();
            }
            $manager->persist($image);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }

    /**
     * Create the mini version of the image.
     * Image is resized to 300x300px. Ratio is not preserved.
     *
     * @param string $source
     * @param string $destination
     * @param int    $width
     * @param int    $height
     */
    private function resizeImage($source, $destination, $width, $height): void
    {
        $sourceImage = imagecreatefromwebp($source);
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        $virtualImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($virtualImage, $sourceImage, 0, 0, 0, 0, $width, $height, $width, $height);
        imagewebp($virtualImage, $destination);

        return;
    }
}
