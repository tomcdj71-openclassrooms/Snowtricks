<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Video;
use App\Form\CommentFormType;
use App\Form\TrickFormType;
use App\Handler\TrickHandler;
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
    private TrickHandler $trickHandler;

    public function __construct(TrickHandler $trickHandler, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->trickHandler = $trickHandler;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $this->getParameter('tricks_per_page');
        if (!is_numeric($limit)) {
            throw new \Exception('Invalid parameter: tricks_per_page');
        }
        $limit = (int) $limit;
        $paginator = $this->trickHandler->findTricksByPage($page, $limit);
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
    public function new(Request $request): Response
    {
        $trick = new \App\Entity\Trick();
        $form = $this->createForm(TrickFormType::class, $trick, ['edit_mode' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickHandler->handleImages($form, $trick);
            $this->trickHandler->handleSlug($trick);
            $this->trickHandler->handleVideos($form, $trick);
            // find a random user to set as author
            // TODO: change this to the current user
            $user = $this->trickHandler->findRandomUser();
            $trick->setAuthor($user);
            $trick->setCreatedAt(new \DateTimeImmutable());
            $this->trickHandler->save($trick);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
        ]);
    }

    #[Route('/trick/{slug}', name: 'app_trick_show', methods: ['GET', 'POST'])]
    public function show(\App\Entity\Trick $trick, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $trickId = $trick->getId();
        if (null === $trickId) {
            throw $this->createNotFoundException('The trick does not exist');
        }
        $limit = $this->getParameter('comments_per_page');
        if (!is_numeric($limit)) {
            throw new \Exception('Invalid parameter: comments_per_page');
        }
        $limit = (int) $limit;
        $paginator = $this->trickHandler->findCommentsByPage($trickId, $page, $limit);
        $comments = iterator_to_array($paginator->getIterator());
        $totalComments = count($paginator);
        $comment = new \App\Entity\Comment();
        $comment->setTrick($trick);
        $comment->setAuthor($this->trickHandler->findRandomUser());
        $comment->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->getRepository(\App\Entity\Comment::class)->save($comment);

            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'total_comments' => $totalComments,
            'limit' => $limit,
            'current_page' => $page,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/trick/{id}/edit', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, \App\Entity\Trick $trick): Response
    {
        $originalTrick = clone $trick;
        $form = $this->createForm(TrickFormType::class, $trick, ['edit_mode' => true]);
        $form->handleRequest($request);

        if (!$trick instanceof \App\Entity\Trick) {
            throw $this->createNotFoundException($this->translator->trans('Trick not found'));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickHandler->handleImages($form, $trick);
            $this->trickHandler->handleVideos($form, $trick);
            if (!$trick->getFeaturedImage() instanceof Image && $originalTrick->getFeaturedImage() instanceof Image) {
                $trick->setFeaturedImage($originalTrick->getFeaturedImage());
            }
            $this->trickHandler->mergeImages($trick, $originalTrick);
            $this->trickHandler->mergeVideos($trick, $originalTrick);
            if ($trick->getVideos() instanceof \Doctrine\Common\Collections\Collection && count($trick->getVideos()) > 0) {
                foreach (array_merge($originalTrick->getVideos()->toArray(), $trick->getVideos()->toArray()) as $video) {
                    $trick->addVideo($video);
                }
            }
            $this->trickHandler->save($trick);
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
    public function delete(Request $request, \App\Entity\Trick $trick): Response
    {
        $token = (string) $request->request->get('_token');
        if (null !== $token && $this->isCsrfTokenValid('delete'.$trick->getId(), $token)) {
            $this->trickHandler->remove($trick);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/trick/delete/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Image $image, Request $request): JsonResponse
    {
        $imageId = $request->attributes->get('id');
        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || !isset($content['_csrf'])) {
            return new JsonResponse(['error' => $this->translator->trans('Invalid request data')], 400);
        }
        $token = (string) $content['_csrf'];
        if ($this->isCsrfTokenValid('delete'.$imageId, $token)) {
            $name = $image->getPath();
            if (null !== $name && $this->trickHandler->delete($name, 'tricks/images', 300, 300)) {
                $trick = $image->getTrick();
                if ($trick instanceof \App\Entity\Trick && $trick->getFeaturedImage() instanceof Image) {
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
        $videoId = $request->attributes->get('id');
        $content = json_decode($request->getContent(), true);
        if (!is_array($content) || !isset($content['_csrf'])) {
            return new JsonResponse(['error' => $this->translator->trans('Invalid request data')], 400);
        }
        $token = (string) $content['_csrf'];
        if ($this->isCsrfTokenValid('delete'.$videoId, $token)) {
            $trick = $video->getTrick();
            if ($trick instanceof \App\Entity\Trick) {
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
