<?php

namespace App\Controller;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\UserRepository;
use App\Controller\AuthController;
use App\Middleware\JwtMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class EquipmentController extends AbstractController
{
    private $jwtManager;
    private $tokenStorageInterface;
    public $userRepository;
    private $jwtMiddleware;

    public function __construct(JwtMiddleware $jwtMiddleware)
    {
        $this->jwtMiddleware = $jwtMiddleware;
    }

    // public function __construct(UserRepository $userRepository, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager)
    // {
    //     $this->jwtManager = $jwtManager;
    //     $this->tokenStorageInterface = $tokenStorageInterface;
    //     $this->userRepository = $userRepository;
    // }

    #[Route('api/equipment/{id}', name:"getEquipment", methods:["GET"])]
    public function showEquipment(int $id, SerializerInterface $serializer, AuthController $authcheck, EquipmentRepository $equipmentRepository, UserRepository $userRepository, Request $request): JsonResponse 
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if($authToken && $userId) {
            $equipment = $equipmentRepository->findEquipmentById($id, $userId);

            if ($equipment) {
                $jsonEquipment = $serializer->serialize($equipment, 'json', ['groups' => 'equipment']);
                return new JsonResponse($jsonEquipment, Response::HTTP_OK, [], true);
            }
            else {
                return new JsonResponse(null, Response::HTTP_NOT_FOUND);
            }
        }
       
        return new JsonResponse("User not found", Response::HTTP_NOT_FOUND);
    }
}
