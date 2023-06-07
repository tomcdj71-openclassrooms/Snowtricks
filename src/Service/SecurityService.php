<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordEncoder,
        private TranslatorInterface $translator,
        private TokenService $tokenService,
        private SendMailService $mail,
        private UrlGeneratorInterface $urlGenerator,
        private string $secret,
        private int $validity,
        private string $emailFrom
    ) {
    }

    public function handleForgotPasswordRequest(string $username): void
    {
        $user = $this->userRepository->findOneByUsername($username);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException($this->translator->trans('User not found.'));
        }

        $payload = ['username' => $user->getUsername(), 'id' => $user->getId()];
        $token = $this->tokenService->generate([], $payload, $this->secret, $this->validity);
        $user->setResetToken($token);
        $this->userRepository->save($user);
        $email = $user->getEmail();
        $expiresAt = (new \DateTime())->modify('+3600 seconds');
        if ($email) {
            $this->mail->send(
                $this->emailFrom,
                $email,
                'Réinitialisation de mot de passe',
                'reset_password',
                [
                    'url' => $this->urlGenerator->generate('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
                    'expiresAt' => $expiresAt,
                ]
            );
        }
    }

    public function handleResetPassword(string $token, string $password): void
    {
        $user = $this->userRepository->findOneByResetToken($token);

        if (!$user instanceof User) {
            throw new \InvalidArgumentException($this->translator->trans('Invalid or expired token.'));
        }

        $user->setResetToken(null);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $password));
        $this->userRepository->save($user);
    }
}
