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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Middleware\JwtMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;


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
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passHasher, SerializerInterface $serializer): JsonResponse {
        try {
            $requestData = json_decode($request->getContent(), true);
            $existingUser = $this->userRepository->findOneBy(['email' => $requestData['email']]);
    
            // check if email exists - unique key
            if ($existingUser) {
                return new JsonResponse(Response::HTTP_CONFLICT, "Email already exists");
            }
    
            $user = new User();
            $user->setEmail($requestData['email'])
                ->setPassword($passHasher->hashPassword($user, $requestData['password']))
                ->setRoles(["ROLE_USER"]);
    
            $consumer = new Consumer();
            $consumer->setFirstName($requestData['first_name'])
                ->setLastName($requestData['last_name'] ?? null)
                ->setPhone($requestData['phone'] ?? null)
                ->setUser($user);
    
            $em->persist($user);
            $em->persist($consumer);
            $em->flush();
    
            $userObject = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
    
            $consumerObject = [
                'id' => $consumer->getId(),
                'first_name' => $consumer->getFirstName(),
                'last_name' => $consumer->getLastName(),
                'phone' => $consumer->getPhone(),
            ];
    
            $response = [
                'message' => 'Registration successful',
                'user' => $userObject,
                'consumer' => $consumerObject,
            ];
    
            $jsonResponse = $serializer->serialize($response, 'json');
            return new JsonResponse($jsonResponse, Response::HTTP_CREATED, [], true);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/reset', name: 'reset_password', methods: ['PUT'])]
    public function resetPassword(EntityManagerInterface $em, Request $request, SerializerInterface $serializer): JsonResponse {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $requestData = json_decode($request->getContent(), true);
            $user = $this->userRepository->find($userId);
    
            if (!$user) {
                return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);
            }
    
            $user->setPassword($this->pass_hasher->hashPassword($user, $requestData['password']));
            $em->flush();
    
            $userObject = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
    
            $response = [
                'message' => 'Password reset successful',
                'user' => $userObject,
            ];
    
            $jsonResponse = $serializer->serialize($response, 'json');
            return new JsonResponse($jsonResponse, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
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