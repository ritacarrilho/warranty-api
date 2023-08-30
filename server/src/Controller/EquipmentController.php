<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Equipment;
use App\Entity\User;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Middleware\JwtMiddleware;
use App\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Repository\WarrantyRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EquipmentController extends AbstractController
{
    private $jwtMiddleware;
    private $equipmentRepository;
    private $warrantyRepository;
    private $documentRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, EquipmentRepository $equipmentRepository, DocumentRepository $documentRepository, WarrantyRepository $warrantyRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->equipmentRepository = $equipmentRepository;
        $this->warrantyRepository = $warrantyRepository;
        $this->documentRepository = $documentRepository;
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

            if (empty($equipments)) {
                return new JsonResponse(['message' => 'No equipment found'], Response::HTTP_NOT_FOUND);
            }
            
            $data = [];

            foreach ($equipments as $equipment) {
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
                    'purchase_date' => $equipment->getFormattedPurchaseDate(),
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
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $requestData = json_decode($request->getContent(), true);

            $categoryRepository = $em->getRepository(Category::class);
            $userRepository = $em->getRepository(User::class);

            // check required fields
            $requiredFields = ['serial_code','category', 'name'];
            foreach ($requiredFields as $field) {
                if (!isset($requestData[$field])) {
                    throw new HttpException(Response::HTTP_BAD_REQUEST, "Missing required field: $field");
                }
            }

            $category = $categoryRepository->find($requestData['category']);
            $user = $userRepository->find($userId);

            // Check if category and user were found
            if (!$category || !$user) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Category or user not found");
            }

            $equipment = new Equipment();
            // check request fields
            $equipment->setName($requestData['name'] ?: null)
                        ->setBrand($requestData['brand'] ?: null)
                        ->setModel($requestData['model'] ?: null)
                        ->setSerialCode($requestData['serial_code'])
                        ->setPurchaseDate(isset($requestData['purchase_date']) ? new \DateTime($requestData['purchase_date']) : null)
                        ->setCategory($category)
                        ->setUser($user);

            $em->persist($equipment);
            $em->flush();

            $equipmentData = [
                'id' => $equipment->getId(),
                'name' => $equipment->getName(),
                'brand' => $equipment->getBrand(),
                'model' => $equipment->getModel(),
                'serial_code' => $equipment->getSerialCode(),
                'purchase_date' => $equipment->getFormattedPurchaseDate(),
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
            // check if equipment exists
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

            $requestData = json_decode($request->getContent(), true);

            // Check if the reference property exists in the request data
            if (!isset($requestData['serial_code'])) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, "Serial code is required");
            }
    
            // Check if the reference is unique
            $existingEquipmentWithCode = $this->equipmentRepository->findOneBy(['serial_code' => $requestData['serial_code']]);
            if ($existingEquipmentWithCode && $existingEquipmentWithCode->getId() !== $currentEquipment->getId()) {
                throw new HttpException(Response::HTTP_CONFLICT, "Equipment with the provided serial code already exists.");
            }

            $updatedEquipment = $serializer->deserialize(
                $request->getContent(),
                Equipment::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentEquipment]
            );
            
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
   public function delete(Equipment $equipment,  EntityManagerInterface $em, Request $request): JsonResponse 
   {
       try {
           $authToken = $request->headers->get('Authorization');
           $userId = $this->jwtMiddleware->getUserId();
   
           if (!$authToken || !$userId) {
               throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
           }
   
           $warranties = $this->warrantyRepository->findBy(['equipment' => $equipment]);
           if ($warranties) {
                // Find and delete associated documents
                foreach ($warranties as $warranty) {
                    $documents = $this->documentRepository->findBy(['warranty' => $warranty]);

                    foreach ($documents as $document) {
                        // TODO: delete files from server
                        $em->remove($document);
                    }
                    
                    $em->remove($warranty);
                }
            }
   
           // Delete the equipment 
           $em->remove($equipment);
           $em->flush();
   
           return new JsonResponse("Equipment, warranties and associated documents deleted successfully", Response::HTTP_OK);
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
            if(!$warranties) {
                return new JsonResponse('No warranties found for this equipment', Response::HTTP_NOT_FOUND);}

            $data = [];

            foreach ($warranties as $warranty) {
                $data[] = [
                    'id' => $warranty->getId(),
                    'reference' => $warranty->getReference(),
                    'start_date' => $warranty->getFormattedStartDate(),
                    'end_date' => $warranty->getFormattedEndDate(),
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