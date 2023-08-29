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
use App\Repository\WarrantyRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EquipmentController extends AbstractController
{
    private $jwtMiddleware;
    private $equipmentRepository;
    private $warrantyRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, EquipmentRepository $equipmentRepository, WarrantyRepository $warrantyRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->equipmentRepository = $equipmentRepository;
        $this->warrantyRepository = $warrantyRepository;
    }

    #[Route('/api/equipments', name:"get_equipments", methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

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
                    'serial_code' => $equipment->getSerialCode(),
                    'purchase_date' => $purchaseDate->format('Y-m-d'),
                    'is_active' => $equipment->isIsActive(),
                    'category_id' => $category['id']
                    // 'category' => $category
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/equipment', name: 'create_equipment', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

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
                ->setSerialCode($requestData['serial_code'])
                ->setPurchaseDate(new \DateTime($requestData['purchase_date']))
                ->setCategory($category)
                ->setUser($user);

            $entityManager->persist($equipment);
            $entityManager->flush();

            $equipmentData = [
                'id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'brand' => $equipment->getBrand(),
                'model' => $equipment->getModel(),
                'serial_code' => $equipment->getSerialCode(),
                'purchase_date' => $equipment->getPurchaseDate()->format('Y-m-d'),
                'category' => [
                    'id' => $category->getId(),
                    'label' => $category->getLabel(),
                ],
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ]
            ];

            $serializedEquipment = $serializer->serialize($equipmentData, 'json');

            $responseData = [
                'message' => 'Equipment created successfully',
                'data' => json_decode($serializedEquipment, true)
            ];
    
            return new JsonResponse($responseData, Response::HTTP_CREATED);
        }  catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('api/equipment/{id}', name:"get_equipment", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer, Request $request): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $equipment = $this->equipmentRepository->findEquipmentById($id, $userId);

            if ($equipment) {
                $jsonEquipment = $serializer->serialize($equipment, 'json', ['groups' => 'equipment']);
                return new JsonResponse($jsonEquipment, Response::HTTP_OK, [], true);
            }

            return new JsonResponse("Equipment item not found", Response::HTTP_NOT_FOUND);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
    

    #[Route('/api/equipment/{id}', name:"update_equipment", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Equipment $currentEquipment, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $updatedEquipment = $serializer->deserialize($request->getContent(), 
                Equipment::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEquipment]
            );
            
            $em->persist($updatedEquipment);
            $em->flush();
            
            $jsonEquipment = $serializer->serialize($updatedEquipment, 'json');

            $responseData = [
                'message' => 'Equipment updated successfully',
                'data' => json_decode($jsonEquipment, true)
            ];

            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
   }


   #[Route('/api/equipment/{id}', name: 'delete_equipment', methods: ['DELETE'])]
   public function delete(Equipment $equipment, ManagerRegistry $doctrine, EntityManagerInterface $em, Request $request): JsonResponse 
   {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }
            $entityManager = $doctrine->getManager();
            $warrantyRepository = $entityManager->getRepository(Warranty::class);
            $warranties = $warrantyRepository->findBy(['equipment' => $equipment]);

            foreach ($warranties as $warranty) {
                $em->remove($warranty);
            }

            $em->remove($equipment);
            $em->flush();

            return new JsonResponse("Equipment and its warranties deleted successfully", Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/equipment/{id}/warranties', name:"get_equipment_warranties", methods: ['GET'])]
    public function listWarranties(Request $request, int $id): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $warranties = $this->warrantyRepository->getWarrantiesForEquipmentAndUser($id, $userId);
            $data = [];

            foreach ($warranties as $warranty) {
                $startDate = $warranty->getStartDate(); // format date
                $endDate = $warranty->getEndDate(); // format date   

                $data[] = [
                    'id' => $warranty->getId(),
                    'reference' => $warranty->getReference(),
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'equipment_id' => $warranty->getEquipment()->getId(),
                    'manufacturer_id' => $warranty->getManufacturer()->getId(),
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}