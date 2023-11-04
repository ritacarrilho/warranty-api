<?php

namespace App\Middleware;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\UserRepository;

class JwtMiddleware
{
    private $jwtManager;
    private $tokenStorage;
    private $userRepository;

    public function __construct(JWTManager $jwtManager, TokenStorageInterface $tokenStorage, UserRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function getUserId(): ?int
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());

        $email = $decodedJwtToken['email'];

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        return $user->getId();
    }

    public function getUserRole(): ?int
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());

        $email = $decodedJwtToken['email'];

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        return $user->getRoles();
    }
}