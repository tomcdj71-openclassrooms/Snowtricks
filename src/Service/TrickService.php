<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrickService
{
    private ImageServiceInterface $imageService;
    private SluggerInterface $slugger;
    private TranslatorInterface $translator;
    private EntityManagerInterface $em;

    public function __construct(ImageServiceInterface $imageService, SluggerInterface $slugger, TranslatorInterface $translator, EntityManagerInterface $em)
    {
        $this->imageService = $imageService;
        $this->slugger = $slugger;
        $this->translator = $translator;
        $this->em = $em;
    }

    /**
     * This method adds an image to a trick.
     *
     * @throws \RuntimeException
     */
    public function addImageToTrick(UploadedFile $image, string $folder, Trick $trick, bool $isFeatured = false): Image
    {
        $file = $this->imageService->add($image, $folder, 300, 300);
        $img = new Image();
        $img->setPath($file);
        $this->em->persist($img);
        if ($isFeatured) {
            return $img;
        } else {
            $trick->addImage($img);
        }

        return $img;
    }

    /**
     * This method handles the slug of the trick.
     *
     * @throws \RuntimeException
     */
    public function handleSlug(Trick $trick): void
    {
        $title = $trick->getTitle();
        if (null === $title) {
            throw new \RuntimeException($this->translator->trans('Invalid title provided.'));
        }
        $slug = $this->slugger->slug($title);
        $trick->setSlug($slug);
    }

    /**
     * This method handles the videos of the trick.
     * It uses the addVideo method to add the videos to the trick.
     *
     * @throws \RuntimeException
     */
    public function handleVideos(FormInterface $form, Trick $trick): void
    {
        $videos = $form->get('videos')->getData();
        $videos = is_iterable($videos) ? $videos : [$videos];
        foreach ($videos as $videoData) {
            $video = new Video();
            $video->setPath($videoData->getPath());
            $trick->addVideo($video);
        }
    }

    /**
     * This method handles the images of the trick.
     * It uses the addImageToTrick method to add the images to the trick.
     *
     * @throws \RuntimeException
     */
    public function handleImages(FormInterface $form, Trick $trick): void
    {
        $images = $form->get('images')->getData();
        if (!is_iterable($images)) {
            throw new \RuntimeException($this->translator->trans('Invalid data provided for images.'));
        }
        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                throw new \RuntimeException($this->translator->trans('Invalid image file.'));
            }
            $this->addImageToTrick($image, 'tricks/images', $trick, false);
        }
        $featuredImage = $form->get('featuredImage')->getData();
        if ($featuredImage instanceof UploadedFile) {
            $image = $this->addImageToTrick($featuredImage, 'tricks/images', $trick, true);
            $trick->setFeaturedImage($image);
        }
    }

    /**
     * This method merges the images of the original trick with the new ones.
     * It is used when the trick is edited.
     *
     * @param Trick $trick         The trick to be edited
     * @param Trick $originalTrick The original trick
     */
    public function mergeImages(Trick $trick, Trick $originalTrick): void
    {
        if ($trick->getImages() instanceof Collection && count($trick->getImages()) > 0) {
            foreach ($trick->getImages() as $image) {
                $originalTrick->getImages()->add($image);
            }
        }
        foreach ($originalTrick->getImages() as $image) {
            $trick->addImage($image);
        }
    }

    /**
     * This method merges the videos of the original trick with the new ones.
     * It is used when the trick is edited.
     *
     * @param Trick $trick         The trick to be edited
     * @param Trick $originalTrick The original trick
     */
    public function mergeVideos(Trick $trick, Trick $originalTrick): void
    {
        if ($trick->getVideos() instanceof Collection && count($trick->getVideos()) > 0) {
            foreach (array_merge($originalTrick->getVideos()->toArray(), $trick->getVideos()->toArray()) as $video) {
                $trick->addVideo($video);
            }
        }
    }
}
