<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\Consumer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Middleware\JwtMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;


class AuthController extends AbstractController
{
    private $userRepository;
    private $pass_hasher;
    private $jwtMiddleware;

    public function __construct(JwtMiddleware $jwtMiddleware, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->pass_hasher = $passwordHasher;
        $this->jwtMiddleware = $jwtMiddleware;
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function createUser(Request $request, PersistenceManagerRegistry $doctrine, UserPasswordHasherInterface $passHasher): JsonResponse {
        try {
            $requestData = json_decode($request->getContent(), true);

            $email = $requestData['email'];
            $existingUser = $this->userRepository->findOneBy(['email' => $email]);

            // check if email exists - unique key
            if ($existingUser) {
                throw new HttpException(Response::HTTP_CONFLICT, "Email already exists");
            }

            $user = new User();
            $user->setEmail($requestData['email'])
                ->setPassword($passHasher->hashPassword($user, $requestData['password']))
                ->setRoles(["ROLE_USER"]);

            $consumer = new Consumer();
            $consumer->setFirstName($requestData['first_name'])
                ->setLastName($requestData['last_name'])
                ->setPhone($requestData['phone'])
                ->setUser($user);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->persist($consumer);
            $entityManager->flush();

            return new JsonResponse("Registration successful", Response::HTTP_CREATED);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/reset', name: 'reset_password', methods: ['PUT'])]
    public function resetPassword(PersistenceManagerRegistry $doctrine, Request $request): JsonResponse {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $requestData = json_decode($request->getContent(), true);
    
            $entityManager = $doctrine->getManager();
            $user = $this->userRepository->find($userId);
    
            if (!$user) {
                return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);
            }
    
            $user->setPassword($this->pass_hasher->hashPassword($user, $requestData['password']));
            $entityManager->flush();
    
            return new JsonResponse("Password reset successful", Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse("An error occurred", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
        
            if ($authToken && $userId) {
                return new JsonResponse(['message' => 'Logout successful'], Response::HTTP_OK);
            }

        } catch (\Exception $e) {
            return new JsonResponse("An error occurred", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}