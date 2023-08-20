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


class ConnexionController extends AbstractController
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


    #[Route('/api/users', name:"get_users", methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $data = [];
 
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
             //    'password' => $user->getPassword(),
                'roles' => $user->getRoles(),
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/api/register', name: 'createUser', methods:['POST'] )]
    public function createUser(Request $request, PersistenceManagerRegistry $doctrine): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $email = $requestData['email'];
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
    
        // check if email exists
        if ($existingUser) {
            return new JsonResponse("Email already exists", Response::HTTP_CONFLICT);
        }
        
        $user = new User();
        $user->setEmail( $requestData['email'])
                ->setPassword( $this->pass_hasher->hashPassword($user, $requestData['password']))
                ->setRoles(["ROLE_USER"]);

        $consumer = new Consumer();
        $consumer->setFirstName($requestData['firstName'])
            ->setLastName($requestData['lastName'])
            ->setPhone($requestData['phone'])
            ->setUser($user);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($user);
        $entityManager->persist($consumer);
        $entityManager->flush();

        return new JsonResponse("Registry successfull", Response::HTTP_CREATED, [], true);
    }


    #[Route('api/users/{id}', name:"get_user", methods:["GET"])]
    public function showUser(int $id, SerializerInterface $serializer): JsonResponse 
    {
        $user = $this->userRepository->find($id);
        if ($user) {
            $jsonUser = $serializer->serialize($user, 'json');
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/users/{id}', name:"updateUser", methods:['PUT'])]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em): JsonResponse 
    {
        $updatedUser = $serializer->deserialize($request->getContent(), 
            User::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );
        
        $em->persist($updatedUser);
        $em->flush();

        $jsonUser = $serializer->serialize($updatedUser, 'json');
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
   }


   #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
   public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse 
   {
       $em->remove($user);
       $em->flush();

       return new JsonResponse("User deleted", Response::HTTP_OK);
   }


    #[Route('/api/reset', name: 'reset_password', methods:['PUT'] )]
    public function resetPassword(PersistenceManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();
    
        if ($authToken && $userId) {
            $requestData = json_decode($request->getContent(), true);
    
            $entityManager = $doctrine->getManager();
            $user = $this->userRepository->find($userId);
    
            if ($user) {
                $user->setPassword($this->pass_hasher->hashPassword($user, $requestData['password']));
    
                $entityManager->flush();
    
                return new JsonResponse("Password reset successful", Response::HTTP_OK);
            }
        }
        return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }
}