<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\Consumer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Middleware\JwtMiddleware;
use App\Repository\ConsumerRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends AbstractController
{
    private $userRepository;
    private $consumerRepository;
    private $pass_hasher;
    private $jwtMiddleware;

    public function __construct(JwtMiddleware $jwtMiddleware, UserRepository $userRepository, ConsumerRepository $consumerRepository,  UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->consumerRepository = $consumerRepository;
        $this->pass_hasher = $passwordHasher;
        $this->jwtMiddleware = $jwtMiddleware;
    }

    // Admin route
    #[Route('/api/users', name:"get_users", methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            if (!$authToken || !$userId) {
                throw new HttpException("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $users = $this->userRepository->findAll();
            $data = [];
     
            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('api/user', name:"get_user", methods:["GET"])]
    public function showUser(SerializerInterface $serializer,Request $request): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            if (!$authToken || !$userId) {
                throw new HttpException("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }

            $user = $this->userRepository->find($userId);
            if ($user) {
                $jsonUser = $serializer->serialize($user, 'json');
                return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
            }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/user', name:"update_user", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            if (!$authToken || !$userId) {
                throw new HttpException("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }

            $updatedUser = $serializer->deserialize($request->getContent(), 
                User::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
            );
            
            $em->persist($updatedUser);
            $em->flush();

            $jsonUser = $serializer->serialize($updatedUser, 'json');
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
   }


   #[Route('/api/user', name: 'delete_user', methods: ['DELETE'])]
   public function deleteUser(PersistenceManagerRegistry $doctrine, EntityManagerInterface $em, Request $request): JsonResponse 
   {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            if (!$authToken || !$userId) {
                throw new HttpException("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $entityManager = $doctrine->getManager();
            $user = $this->userRepository->find($userId);
            $consumer = $this->consumerRepository->findByUser($userId);

            $entityManager->remove($user);
            $entityManager->remove($consumer);
            $entityManager->flush();

            return new JsonResponse("User deleted", Response::HTTP_OK);
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
   }
}