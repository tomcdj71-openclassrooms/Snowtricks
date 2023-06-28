<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private TokenService $tokenService,
        private SendMailService $sendMailService,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function registerUser(User $user, string $plainPassword, string $secret): void
    {
        // Hash the user's password
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));

        // Persist the user to the database
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
