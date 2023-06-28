<?php

namespace App\Handler;

use App\Entity\Trick;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use App\Service\ImageService;
use App\Service\TrickService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Form\FormInterface;

class TrickHandler
{
    private TrickService $trickService;
    private ImageService $imageService;
    private TrickRepository $trickRepository;
    private UserRepository $userRepository;
    private CommentRepository $commentRepository;

    public function __construct(
        TrickService $trickService,
        ImageService $imageService,
        TrickRepository $trickRepository,
        UserRepository $userRepository,
        CommentRepository $commentRepository
    ) {
        $this->trickService = $trickService;
        $this->imageService = $imageService;
        $this->trickRepository = $trickRepository;
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository;
    }

    public function delete(string $image, string $directory, int $width, int $height): bool
    {
        return $this->imageService->delete($image, $directory, $width, $height);
    }

    public function findCommentsByPage(int $trickId, int $page, int $limit): Paginator
    {
        return $this->commentRepository->findCommentsByPage($trickId, $page, $limit);
    }

    public function findRandomUser(): ?User
    {
        return $this->userRepository->findRandomUser();
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
}
