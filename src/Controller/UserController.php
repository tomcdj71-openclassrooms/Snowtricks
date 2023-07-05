<?php

namespace App\Controller;

use App\Entity\Image;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(private ImageService $imageService, private EntityManagerInterface $entityManager)
    {
        $this->imageService = $imageService;
        $this->entityManager = $entityManager;
    }

    #[Route('/user', name: 'app_user')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(\App\Form\AvatarFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('avatar')->getData();
            if ($file instanceof UploadedFile) {
                $oldAvatar = $user->getAvatar();
                if ($oldAvatar instanceof \App\Entity\Image) {
                    $oldAvatarPath = $oldAvatar->getPath();
                    if ($oldAvatarPath && ImageService::DEFAULT_FILE !== $oldAvatarPath) {
                        $this->imageService->deleteUserAvatar($oldAvatarPath);
                        $this->entityManager->remove($oldAvatar);
                        $this->entityManager->flush();
                    }
                }
                $fileName = $this->imageService->addUserAvatar($file, 800, 800);
                $image = new Image();
                $image->setPath($fileName);
                $image->setUser($user);
                $user->setAvatar($image);
                $this->entityManager->persist($image);
                $this->entityManager->flush();
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
