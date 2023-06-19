<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\TrickFormType;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use App\Service\ImageService;
use App\Service\TrickService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrickController extends AbstractController
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private TrickService $trickService;

    public function __construct(TrickService $trickService, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->trickService = $trickService;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(TrickRepository $trickRepository, Request $request): Response
    {
        $limit = 8;
        $page = $request->query->getInt('page', 1);
        $paginator = $trickRepository->findTricksByPage($page, $limit);
        $tricks = iterator_to_array($paginator->getIterator());
        $totalTricks = count($paginator);

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
            'total_tricks' => $totalTricks,
            'current_page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/trick/new', name: 'app_trick_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TrickRepository $trickRepository, UserRepository $userRepository): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick, ['edit_mode' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickService->handleImages($form, $trick);
            $this->trickService->handleSlug($trick);
            $this->trickService->handleVideos($form, $trick);
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
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    #[Route('/trick/{id}/edit', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $originalTrick = clone $trick;
        $form = $this->createForm(TrickFormType::class, $trick, ['edit_mode' => true]);
        $form->handleRequest($request);

        if (!$trick instanceof Trick) {
            throw $this->createNotFoundException($this->translator->trans('Trick not found'));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickService->handleImages($form, $trick);
            $this->trickService->handleVideos($form, $trick);
            if (!$trick->getFeaturedImage() instanceof Image && $originalTrick->getFeaturedImage() instanceof Image) {
                $trick->setFeaturedImage($originalTrick->getFeaturedImage());
            }
            $this->trickService->mergeImages($trick, $originalTrick);
            $this->trickService->mergeVideos($trick, $originalTrick);
            if ($trick->getVideos() instanceof Collection && count($trick->getVideos()) > 0) {
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
        $imageId = $request->attributes->get('id');
        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || !isset($content['_csrf'])) {
            return new JsonResponse(['error' => $this->translator->trans('Invalid request data')], 400);
        }
        $token = (string) $content['_csrf'];
        if ($this->isCsrfTokenValid('delete'.$imageId, $token)) {
            $name = $image->getPath();
            if (null !== $name && $imageService->delete($name, 'tricks/images', 300, 300)) {
                $trick = $image->getTrick();
                if ($trick instanceof Trick) {
                    if ($image === $trick->getFeaturedImage()) {
                        $trick->setFeaturedImage(null);
                    }
                    $this->entityManager->persist($trick);
                    $this->entityManager->remove($image);
                    $this->entityManager->flush();

                    return new JsonResponse(['success' => true], 200);
                }

                return new JsonResponse(['success' => true], 200);
            }

            return new JsonResponse(['error' => $this->translator->trans('Error encountered during the deletion of the image')], 400);
        }

        return new JsonResponse(['error' => 'Invalid token'], 400);
    }

    #[Route('/trick/delete/video/{id}', name: 'delete_video', methods: ['DELETE'])]
    public function deleteVideo(Video $video, Request $request): JsonResponse
    {
        $token = (string) $request->request->get('_token', '');
        dump($request->request->get('_token'));
        if ($this->isCsrfTokenValid('delete-video'.$video->getId(), $token)) {
            $trick = $video->getTrick();
            if ($trick instanceof Trick) {
                $trick->removeVideo($video);
                $this->entityManager->persist($trick);
                $this->entityManager->remove($video);
                $this->entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            }

            return new JsonResponse(['error' => $this->translator->trans('Error encountered during the deletion of the video')], 400);
        }

        return new JsonResponse(['error' => 'Invalid token'], 400);
    }
}
