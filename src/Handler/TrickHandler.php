<?php

namespace App\Handler;

use App\Entity\Trick;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Service\ImageService;
use App\Service\TrickService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Form\FormInterface;

class TrickHandler
{
    public function __construct(
        private TrickService $trickService,
        private ImageService $imageService,
        private TrickRepository $trickRepository,
        private CommentRepository $commentRepository,
        private EntityManagerInterface $entityManager
    ) {
        $this->trickService = $trickService;
        $this->imageService = $imageService;
        $this->trickRepository = $trickRepository;
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
    }

    public function delete(string $image, string $type): bool
    {
        return $this->imageService->delete($image, $type);
    }

    public function findCommentsByPage(int $trickId, int $page, int $limit): Paginator
    {
        return $this->commentRepository->findCommentsByPage($trickId, $page, $limit);
    }

    public function findTricksByPage(int $page, int $limit): Paginator
    {
        return $this->trickRepository->findTricksByPage($page, $limit);
    }

    public function handleImages(FormInterface $form, Trick $trick): void
    {
        $this->trickService->handleImages($form, $trick);
    }

    public function handleSlug(Trick $trick): void
    {
        $this->trickService->handleSlug($trick);
    }

    public function handleVideos(FormInterface $form, Trick $trick): void
    {
        $this->trickService->handleVideos($form, $trick);
    }

    public function mergeImages(Trick $trick, Trick $originalTrick): void
    {
        $this->trickService->mergeImages($trick, $originalTrick);
    }

    public function mergeVideos(Trick $trick, Trick $originalTrick): void
    {
        $this->trickService->mergeVideos($trick, $originalTrick);
    }

    public function remove(Trick $trick): void
    {
        $this->trickRepository->remove($trick);
    }

    public function save(Trick $trick): void
    {
        $this->trickRepository->save($trick);
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function findOneBy(array $criteria): ?Trick
    {
        return $this->trickRepository->findOneBy($criteria);
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
