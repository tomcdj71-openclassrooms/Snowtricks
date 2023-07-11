<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private TokenService $tokenService,
        private SendMailService $sendMailService,
        private UrlGeneratorInterface $urlGenerator,
        private ImageService $imageService,
        private ParameterBagInterface $params,
    ) {
    }

    public function registerUser(User $user, string $plainPassword, string $secret): void
    {
        // Hash the user's password
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

        // Set default avatar for the user
        $projectDir = $this->params->get('kernel.project_dir');
        if (!is_string($projectDir)) {
            throw new \RuntimeException('Kernel project directory path should be a string.');
        }
        $defaultAvatarPath = $projectDir.'/public/'.ImageService::IMAGE_DIRECTORY.'/'.ImageService::DEFAULT_FILE;
        $defaultAvatar = new UploadedFile($defaultAvatarPath, ImageService::DEFAULT_FILE, null, null, true);
        $width = 250;
        $height = 250;
        $avatarFileName = $this->imageService->addUserAvatar($defaultAvatar, $width, $height);
        $image = new Image();
        $image->setPath($avatarFileName);
        $image->setUser($user);
        $user->setAvatar($image);
        $this->entityManager->persist($image);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Generate a token for email verification
        $payload = ['email' => $user->getEmail(), 'id' => $user->getId()];
        $validity = 3600;
        if (is_string($secret)) {
            $token = $this->tokenService->generate([], $payload, $secret, $validity);
            $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('PT'.$validity.'S'));

            // Send the verification email
            $this->sendMailService->send(
                'your-email@domain.com',
                $user->getEmail() ?? '',
                'Email Verification',
                'confirmation_email',
                [
                    'url' => $this->urlGenerator->generate('app_verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                    'expiresAt' => $expiresAt,
                ]
            );
        }
    }

    public function verifyUserEmail(User $user, string $token, string $secret): void
    {
        if (is_string($secret) && $this->tokenService->check($token, $secret)) {
            // Verify and save the user
            $user->setIsVerified(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
