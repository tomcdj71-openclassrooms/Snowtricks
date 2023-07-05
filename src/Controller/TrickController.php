<?php

namespace App\Controller;

use App\Handler\TrickHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrickController extends AbstractController
{
    public function __construct(private TrickHandler $trickHandler, private TranslatorInterface $translator)
    {
        $this->trickHandler = $trickHandler;
        $this->translator = $translator;
    }

    #[Route('', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $this->getParameter('tricks_per_page');
        if (!is_numeric($limit)) {
            throw new \Exception($this->translator->trans('Invalid parameter: tricks_per_page'));
        }
        $limit = (int) $limit;
        $paginator = $this->trickHandler->findTricksByPage($page, $limit);
        $tricks = iterator_to_array($paginator->getIterator());
        $totalTricks = count($paginator);
        $hasMoreTricks = $totalTricks > ($page * $limit);

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
            'total_tricks' => $totalTricks,
            'current_page' => $page,
            'limit' => $limit,
            'has_more_tricks' => $hasMoreTricks,
        ]);
    }

    #[Route('/trick/new', name: 'app_trick_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $trick = new \App\Entity\Trick();
        $form = $this->createForm(\App\Form\TrickFormType::class, $trick, ['edit_mode' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickHandler->handleImages($form, $trick);
            $this->trickHandler->handleSlug($trick);
            $this->trickHandler->handleVideos($form, $trick);
            $author = $this->getUser();
            if ($author instanceof \App\Entity\User) {
                $trick->setAuthor($author);
            }
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
    public function show(Request $request): Response
    {
        $slug = $request->attributes->get('slug');
        $trick = $this->trickHandler->findOneBy(['slug' => $slug]);
        if ($trick instanceof \App\Entity\Trick) {
            $trickId = $trick->getId();
            $page = $request->query->getInt('page', 1);
            if (null === $trickId) {
                throw $this->createNotFoundException($this->translator->trans('The trick does not exist'));
            }
            $limit = $this->getParameter('comments_per_page');
            if (!is_numeric($limit)) {
                throw new \Exception($this->translator->trans('Invalid parameter: comments_per_page'));
            }
            $limit = (int) $limit;
            $paginator = $this->trickHandler->findCommentsByPage($trickId, $page, $limit);
            $comments = iterator_to_array($paginator->getIterator());
            $totalComments = count($paginator);
            $comment = new \App\Entity\Comment();
            $comment->setTrick($trick);
            $user = $this->getUser();
            if ($user instanceof \App\Entity\User) {
                $comment->setAuthor($user);
            }
            $comment->setCreatedAt(new \DateTimeImmutable());
            $form = $this->createForm(\App\Form\CommentFormType::class, $comment);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->trickHandler->getEntityManager()->getRepository(\App\Entity\Comment::class)->save($comment);
                $this->trickHandler->getEntityManager()->persist($comment);
                $this->trickHandler->getEntityManager()->flush();

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

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/trick/{id}/edit', name: 'app_trick_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $id = $request->attributes->get('id');
        $trick = $this->trickHandler->findOneBy(['id' => $id]);

        if (!$trick instanceof \App\Entity\Trick) {
            throw $this->createNotFoundException($this->translator->trans('Trick not found'));
        }

        $originalTrick = clone $trick;
        $form = $this->createForm(\App\Form\TrickFormType::class, $trick, ['edit_mode' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickHandler->handleImages($form, $trick);
            $this->trickHandler->handleVideos($form, $trick);

            $featuredImage = $trick->getFeaturedImage();
            $clonedFeaturedImage = $originalTrick->getFeaturedImage();

            if (!$featuredImage instanceof \App\Entity\Image && $clonedFeaturedImage instanceof \App\Entity\Image) {
                $trick->setFeaturedImage($clonedFeaturedImage);
            }

            $this->trickHandler->mergeImages($trick, $originalTrick);
            $this->trickHandler->mergeVideos($trick, $originalTrick);

            $trickVideos = $trick->getVideos();
            if ($trickVideos instanceof \Doctrine\Common\Collections\Collection && count($trickVideos) > 0) {
                foreach (array_merge($originalTrick->getVideos()->toArray(), $trickVideos->toArray()) as $video) {
                    $trick->addVideo($video);
                }
            }

            if ($trick->getTitle() !== $originalTrick->getTitle()) {
                $this->trickHandler->handleSlug($trick);
            }
            $this->trickHandler->save($trick);
            $this->addFlash('success', $this->translator->trans('Trick updated successfully!')
            );

            return $this->redirectToRoute('app_trick_show', ['slug' => $trick->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return new Response($this->renderView('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]));
    }

    #[Route('/trick/delete/{id}', name: 'app_trick_delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        $id = $request->attributes->get('id');
        $trick = $this->trickHandler->findOneBy(['id' => $id]);
        if ($trick instanceof \App\Entity\Trick) {
            $token = (string) $request->request->get('_token');
            if (null !== $token && $this->isCsrfTokenValid('delete'.$trick->getId(), $token)) {
                $this->trickHandler->remove($trick);
            }
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
