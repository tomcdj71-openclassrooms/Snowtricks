<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\TrickFormType;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use App\Service\ImageService;
use App\Service\ImageServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrickController extends AbstractController
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

    #[Route('', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(TrickRepository $trickRepository): Response
    {
        return $this->render('trick/index.html.twig', [
            'tricks' => $trickRepository->findAll(),
        ]);
    }

    #[Route('/trick/new', name: 'app_trick_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TrickRepository $trickRepository, UserRepository $userRepository): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImages($form, $trick);
            $this->handleSlug($trick);
            $this->handleVideos($form, $trick);
            // find a random user to set as author
            // TODO: change this to the current user
            $user = $userRepository->findRandomUser();
            $trick->setAuthor($user);
            $trick->setCreatedAt(new \DateTimeImmutable());
            $trickRepository->save($trick);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/trick/{slug}', name: 'app_trick_show', methods: ['GET'])]
    public function show(Trick $trick): Response
    {
        dump($trick);

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    #[Route('/trick/{id}/edit', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $originalTrick = clone $trick; // Keep a copy of the original trick object before changes

        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        $trick = $trickRepository->find($trick->getId());
        if (!$trick instanceof Trick) {
            throw $this->createNotFoundException($this->translator->trans('Trick not found'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // If featuredImage is null, restore it to its original state
            if (!$trick->getFeaturedImage() instanceof \App\Entity\Image && $originalTrick->getFeaturedImage() instanceof \App\Entity\Image) {
                $trick->setFeaturedImage($originalTrick->getFeaturedImage());
            }

            // If new images/videos have been added, merge them with the existing ones
            if ($trick->getImages() instanceof \Doctrine\Common\Collections\Collection && count($trick->getImages()) > 0) {
                foreach (array_merge($originalTrick->getImages()->toArray(), $trick->getImages()->toArray()) as $image) {
                    $trick->addImage($image);
                }
            }

            if ($trick->getVideos() instanceof \Doctrine\Common\Collections\Collection && count($trick->getVideos()) > 0) {
                foreach (array_merge($originalTrick->getVideos()->toArray(), $trick->getVideos()->toArray()) as $video) {
                    $trick->addVideo($video);
                }
            }

            $trickRepository->save($trick);
            $this->addFlash(
                'success',
                $this->translator->trans('Trick updated successfully!')
            );

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return new Response($this->renderView('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]));
    }

    #[Route('/trick/{id}', name: 'app_trick_delete', methods: ['POST'])]
    public function delete(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $token = (string) $request->request->get('_token');
        if (null !== $token && $this->isCsrfTokenValid('delete'.$trick->getId(), $token)) {
            $trickRepository->remove($trick);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/trick/delete/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Image $image, Request $request, ImageService $imageService): JsonResponse
    {
        $token = (string) $request->request->get('_token', '');
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $token)) {
            $name = $image->getPath();
            if (null !== $name && $imageService->delete($name, 'tricks/images', 300, 300)) {
                $trick = $image->getTrick();
                if ($trick instanceof \App\Entity\Trick) {
                    if ($image === $trick->getFeaturedImage()) {
                        $trick->setFeaturedImage(null);
                    }
                    $this->em->persist($trick);
                    $this->em->remove($image);
                    $this->em->flush();

                    return new JsonResponse(['success' => true], 200);
                }

                return new JsonResponse(['success' => true], 200);
            }

            return new JsonResponse(['error' => $this->translator->trans('Error encountered during the deletion of the image')], 400);
        }

        return new JsonResponse(['error' => 'Invalid token'], 400);
    }

    /**
     * This method handles the images of a trick.
     */
    private function handleImages(FormInterface $form, Trick $trick): void
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
     * This method adds an image to a trick.
     */
    private function addImageToTrick(UploadedFile $image, string $folder, Trick $trick, bool $isFeatured = false): Image
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
     * It uses the slugger service to create a slug from the title of the trick.
     */
    private function handleSlug(Trick $trick): void
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
     */
    private function handleVideos(FormInterface $form, Trick $trick): void
    {
        $videos = $form->get('videos')->getData();
        $videos = is_iterable($videos) ? $videos : [$videos];
        foreach ($videos as $videoData) {
            $video = new Video();
            $video->setPath($videoData->getPath());
            $trick->addVideo($video);
        }
    }
}
