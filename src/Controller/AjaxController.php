<?php

namespace App\Controller;

use App\Handler\TrickHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/load_more', name: 'load_more_')]
class AjaxController extends AbstractController
{
    public function __construct(
        private TrickHandler $trickHandler,
        private TranslatorInterface $translator
    ) {
        $this->trickHandler = $trickHandler;
        $this->translator = $translator;
    }

    #[Route('/tricks/{page}', name: 'load_more_tricks', methods: ['GET'])]
    public function loadMoreTricks(int $page): Response
    {
        $limit = $this->getParameter('tricks_per_page');
        if (!is_numeric($limit)) {
            throw new \Exception($this->translator->trans('Invalid parameter: comments_per_page'));
        }
        $limit = (int) $limit;
        $tricks = $this->trickHandler->findTricksByPage($page, $limit);

        return $this->render('_partials/_more.html.twig', ['items' => $tricks, 'type' => 'trick']);
    }

    #[Route('/comments/{trickId}/{page}', name: 'load_more_comments', methods: ['GET'])]
    public function loadMoreComments(int $trickId, int $page): Response
    {
        $limit = $this->getParameter('comments_per_page');
        if (!is_numeric($limit)) {
            throw new \Exception($this->translator->trans('Invalid parameter: comments_per_page'));
        }
        $limit = (int) $limit;
        $comments = $this->trickHandler->findCommentsByPage($trickId, $page, $limit);

        return $this->render('_partials/_more.html.twig', ['items' => $comments, 'type' => 'comment']);
    }
}
