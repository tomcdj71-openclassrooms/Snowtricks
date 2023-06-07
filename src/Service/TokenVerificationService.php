<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class TokenVerificationService
{
    public function __construct(private UserRepository $userRepository, private TokenService $tokenService)
    {
    }

    public function validateToken(string $token, string $secret): ?User
    {
        if (!$this->tokenService->isValid($token, $secret) || $this->tokenService->isExpired($token)) {
            return null;
        }

        $payload = $this->tokenService->getPayload($token);

        return $this->userRepository->find($payload['id']);
    }
}
