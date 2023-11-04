<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Middleware\JwtMiddleware;
use App\Repository\ConsumerRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends AbstractController
{
    private $userRepository;
    private $consumerRepository;
    private $jwtMiddleware;

    public function __construct(JwtMiddleware $jwtMiddleware, UserRepository $userRepository, ConsumerRepository $consumerRepository)
    {
        $this->userRepository = $userRepository;
        $this->consumerRepository = $consumerRepository;
        $this->jwtMiddleware = $jwtMiddleware;
    }

    // Admin route
    #[Route('/api/users', name:"get_users", methods: ['GET'])]
    public function listUsers(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            // check user and token
            if (!$authToken || !$userId) {
                return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $users = $this->userRepository->findAll();
            $data = [];
     
            // response object, avoid to send passwords
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
    public function showUser(SerializerInterface $serializer, Request $request): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            // check user and token
            if (!$authToken || !$userId) {
                return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $user = $this->userRepository->find($userId);
            // response obj to not send password
            if ($user) {
                $responseData = [
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles(),
                ];

            $jsonResponse = $serializer->serialize($responseData, 'json');

            return new JsonResponse($jsonResponse, Response::HTTP_OK, [], true);
            } else {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/user', name:"update_user", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Security $security): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            // check user and token
            if (!$authToken || !$userId) {
                return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $requestData = json_decode($request->getContent(), true);
    
            // Validate and ensure the email is sent in the request body
            if (!isset($requestData['email'])) {
                return new JsonResponse(Response::HTTP_BAD_REQUEST, "Email is required");
            }

            // Check if the email is unique
            $existingUser = $this->userRepository->findOneBy(['email' => $requestData['email']]);
            if ($existingUser) {
                $authenticatedUser = $security->getUser();
                if ($existingUser !== $authenticatedUser) {
                    return new JsonResponse(Response::HTTP_CONFLICT, "Email is already in use.");
                }
            }

            // Deserialize the user entity
            $updatedUser = $serializer->deserialize(
                $request->getContent(), 
                User::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $authenticatedUser]
            );
        
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
   public function deleteUser(EntityManagerInterface $em, Request $request): JsonResponse 
   {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
            
            // check user and token
            if (!$authToken || !$userId) {
                return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }

            // Find and verify the user to delete
            $user = $this->userRepository->find($userId);
            if (!$user) {
                return new JsonResponse(Response::HTTP_NOT_FOUND, "User not found");
            }
    
            // Find and verify the consumer to delete
            $consumer = $this->consumerRepository->findByUser($userId);
            if (!$consumer) {
                return new JsonResponse(Response::HTTP_NOT_FOUND, "Consumer not found");
            }
    
            // persist and flush into DB
            $em->remove($user);
            $em->remove($consumer);
            $em->flush();

            return new JsonResponse("User deleted", Response::HTTP_OK);
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
   }
}