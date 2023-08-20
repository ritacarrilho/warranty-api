<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Equipment;
use App\Entity\User;
use App\Entity\Warranty;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Middleware\JwtMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


class EquipmentController extends AbstractController
{
    private $jwtMiddleware;
    private $equipmentRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, EquipmentRepository $equipmentRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->equipmentRepository = $equipmentRepository;
    }

    #[Route('/api/equipments', name:"get_equipments", methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if($authToken && $userId) {
            $equipments = $this->equipmentRepository->findByUser($userId);
            $data = [];

            foreach ($equipments as $equipment) {
                $purchaseDate = $equipment->getPurchaseDate(); // format date

                $category = [
                    'id' => $equipment->getCategory()->getId(),
                    'label' => $equipment->getCategory()->getLabel(),
                ];

                $data[] = [
                    'id' => $equipment->getId(),
                    'name' => $equipment->getName(),
                    'brand' => $equipment->getBrand(),
                    'model' => $equipment->getModel(),
                    'picture' => $equipment->getPicture(),
                    'serial_code' => $equipment->getSerialCode(),
                    'purchase_date' => $purchaseDate->format('Y-m-d'),
                    'is_active' => $equipment->isIsActive(),
                    'category' => $category
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        }
        return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/equipments', name: 'create_equipment', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse 
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if ($authToken && $userId) {
            $requestData = json_decode($request->getContent(), true);

            $entityManager = $doctrine->getManager();
            $categoryRepository = $entityManager->getRepository(Category::class);
            $userRepository = $entityManager->getRepository(User::class);

            $category = $categoryRepository->find($requestData['category']);
            $user = $userRepository->find($userId);

            $equipment = new Equipment();
            $equipment->setName($requestData['name'])
                ->setBrand($requestData['brand'])
                ->setModel($requestData['model'])
                ->setPicture($requestData['picture'])
                ->setSerialCode($requestData['serial_code'])
                ->setPurchaseDate(new \DateTime($requestData['purchase_date']))
                ->setCategory($category)
                ->setUser($user);

            $entityManager->persist($equipment);
            $entityManager->flush();

            return new JsonResponse("Equipment created successfully", Response::HTTP_CREATED, [], true);
        }

        return new JsonResponse("Bad request", Response::HTTP_NOT_FOUND);
    }


    #[Route('api/equipments/{id}', name:"getEquipment", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer, Request $request): JsonResponse 
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if($authToken && $userId) {
            $equipment = $this->equipmentRepository->findEquipmentById($id, $userId);

            if ($equipment) {
                $jsonEquipment = $serializer->serialize($equipment, 'json', ['groups' => 'equipment']);
                return new JsonResponse($jsonEquipment, Response::HTTP_OK, [], true);
            }

            return new JsonResponse("Equipment item not found", Response::HTTP_NOT_FOUND);
        }
       
        return new JsonResponse("Bad request", Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/equipments/{id}', name:"update_equipment", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Equipment $currentEquipment, EntityManagerInterface $em): JsonResponse 
    {
        $updatedEquipment = $serializer->deserialize($request->getContent(), 
            Equipment::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEquipment]
        );
        
        $em->persist($updatedEquipment);
        $em->flush();

        $jsonEquipment = $serializer->serialize($updatedEquipment, 'json');
        return new JsonResponse("Equipment updated successfully", Response::HTTP_OK, [], true);
   }


   #[Route('/api/equipments/{id}', name: 'delete_equipment', methods: ['DELETE'])]
   public function delete(Equipment $equipment, ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request): JsonResponse 
   {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if ($authToken && $userId) {
            $entityManager = $doctrine->getManager();
            $warrantyRepository = $entityManager->getRepository(Warranty::class);
            $warranties = $warrantyRepository->findBy(['equipment' => $equipment]);

            foreach ($warranties as $warranty) {
                $em->remove($warranty);
            }

            $em->remove($equipment);
            $em->flush();

            return new JsonResponse("Equipment and its warranties deleted successfully", Response::HTTP_OK);
        }

        return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
    }
}