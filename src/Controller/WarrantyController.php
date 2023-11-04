<?php

namespace App\Controller;

use App\Entity\Warranty;
use App\Repository\WarrantyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Middleware\JwtMiddleware;
use App\Repository\DocumentRepository;
use App\Repository\EquipmentRepository;
use App\Repository\ManufacturerRepository;
use App\Service\DocumentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WarrantyController extends AbstractController
{
    private $jwtMiddleware;
    private $warrantyRepository;
    private $documentRepository;
    private $equipmentRepository;
    private $manufacturerRepository;


    public function __construct(JwtMiddleware $jwtMiddleware, WarrantyRepository $warrantyRepository, DocumentRepository $documentRepository, EquipmentRepository $equipmentRepository, ManufacturerRepository $manufacturerRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->warrantyRepository = $warrantyRepository;
        $this->documentRepository = $documentRepository;
        $this->equipmentRepository = $equipmentRepository;
        $this->manufacturerRepository = $manufacturerRepository;
    }

    #[Route('/api/warranties', name: 'get_warranties', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            $warranties = $this->warrantyRepository->findOneByUser($userId);

            if (empty($warranties)) {
                return new JsonResponse(['message' => 'No warranty found'], Response::HTTP_NOT_FOUND);
            }
            
            $data = [];
    
            foreach ($warranties as $warranty) {
                $manufacturerId = null;
                $manufacturer = $warranty->getManufacturer();
                
                if ($manufacturer !== null) {
                    $manufacturerId = $manufacturer->getId();
                }
    
                $data[] = [
                    'id' => $warranty->getId(),
                    'reference' => $warranty->getReference(),
                    'start_date' => $warranty->getFormattedStartDate(),
                    'end_date' => $warranty->getFormattedEndDate(),
                    'equipment_id' => $warranty->getEquipment()->getId(),
                    'manufacturer_id' => $manufacturerId,
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/warranty', name: 'create_warranty', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            $requestData = json_decode($request->getContent(), true);
    
            $equipment = $this->equipmentRepository->find($requestData['equipment_id']);
            if(!$equipment) {
                return new JsonResponse(['message' => 'Invalid equipment'], Response::HTTP_NOT_FOUND);
            }

            // Check if reference property exists in the request data
            if (!isset($requestData['reference'])) {
                return new JsonResponse(Response::HTTP_BAD_REQUEST, "Reference is required");
            }

            // Check if reference is unique
            $existingWarrantyWithReference = $this->warrantyRepository->findOneBy(['reference' => $requestData['reference']]);
            if ($existingWarrantyWithReference) {
                return new JsonResponse(Response::HTTP_CONFLICT, "Warranty with the provided reference already exists.");
            }

            // Verify if manufacturer_id is in the request data
            $manufacturerId = isset($requestData['manufacturer_id']) ? $requestData['manufacturer_id'] : null;

            // Only fetch manufacturer if manufacturer_id is in request
            $manufacturer = null;
            if ($manufacturerId !== null) {
                $manufacturer = $this->manufacturerRepository->find($manufacturerId);
            }
    
            $warranty = new Warranty();
            $warranty->setReference($requestData['reference'])
                    ->setStartDate(new \DateTime($requestData['start_date']))
                    ->setEndDate(new \DateTime($requestData['end_date']))
                    ->setEquipment($equipment)
                    ->setManufacturer($manufacturer);
    
            $em->persist($warranty);
            $em->flush();

            $warrantyData = [
                'id' => $warranty->getId(),
                'reference' => $warranty->getReference(),
                'start_date' => $warranty->getStartDate()->format('Y-m-d'),
                'end_date' => $warranty->getEndDate()->format('Y-m-d'),
                'equipment_id' => $warranty->getEquipment()->getId(),
                'manufacturer_id' => $manufacturerId,
            ];
    
            $serializedWarranty = $serializer->serialize($warrantyData, 'json');

            $responseData = [
                'message' => 'Warranty created successfully',
                'data' => json_decode($serializedWarranty, true)
            ];
    
            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/warranty/{id}', name:"get_warranty_manufacturer_docs", methods:["GET"])]
    public function showWithManufacturerAndDocs(int $id, SerializerInterface $serializer, Request $request): JsonResponse {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse(Response::HTTP_BAD_REQUEST, "Bad request");
            }
    
            $warranty = $this->warrantyRepository->find($id);
            if (!$warranty) {
                return new JsonResponse("Warranty not found", Response::HTTP_NOT_FOUND);
            }
    
            $manufacturer = $warranty->getManufacturer();
            $documents = $this->documentRepository->findByWarranty($id);
            $documentObj = [];
    
            if (!empty($documents) ) {
                foreach ($documents as $document) {
                    $documentObj[] = [
                        'id' => $document->getId(),
                        'name' => $document->getName(),
                        'path' => $document->getPath(),
                    ];
                }
            }
    
            $warrantyObject = [
                'id' => $warranty->getId(),
                'reference' => $warranty->getReference(),
                'start_date' => $warranty->getFormattedStartDate(),
                'end_date' => $warranty->getFormattedEndDate(),
                'equipment_id' => $warranty->getEquipment()->getId(),
                'manufacturer' => $manufacturer ? [
                    'id' => $manufacturer->getId(),
                    'name' => $manufacturer->getName(),
                    'email' => $manufacturer->getEmail(),
                    'phone' => $manufacturer->getPhone(),
                    'address' => $manufacturer->getAddress(),
                    'zip_code' => $manufacturer->getZipCode(),
                    'city' => $manufacturer->getCity(),
                    'country' => $manufacturer->getCountry(),
                ] : null,
                'documents' => $documentObj
            ];
    
            $jsonWarranty = $serializer->serialize($warrantyObject, 'json');
            return new JsonResponse($jsonWarranty, Response::HTTP_OK, [], true);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/warranty/{id}', name:"update_warranty", methods:['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $warranty = $this->warrantyRepository->find($id);
            if (!$warranty) {
                return new JsonResponse("Warranty not found", Response::HTTP_NOT_FOUND);
            }

            $requestData = json_decode($request->getContent(), true);

            // Check if the reference property exists in the request data
            if (!isset($requestData['reference'])) {
                return new JsonResponse(Response::HTTP_BAD_REQUEST, "Warranty reference is required.");
            }

            // Check if the reference is unique
            $existingWarrantyWithReference = $this->warrantyRepository->findOneBy(['reference' => $requestData['reference']]);
            if ($existingWarrantyWithReference && $existingWarrantyWithReference->getId() !== $warranty->getId()) {
                return new JsonResponse(Response::HTTP_CONFLICT, "Warranty with the provided reference already exists.");
            }

            // Update the warranty data
            $warranty->setReference($requestData['reference']);

            $em->flush();

            $warrantyData = [
                'id' => $warranty->getId(),
                'reference' => $warranty->getReference(),
                'start_date' => $warranty->getFormattedStartDate(),
                'end_date' => $warranty->getFormattedEndDate(),
                'equipment_id' => $warranty->getEquipment()->getId(),
                'manufacturer_id' => $warranty->getManufacturer() ? $warranty->getManufacturer()->getId() : null,
            ];

            $serializedWarranty = $serializer->serialize($warrantyData, 'json');

            $responseData = [
                'message' => 'Warranty updated successfully',
                'data' => json_decode($serializedWarranty, true)
            ];

            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/warranty/{id}', name:"delete_warranty", methods:['DELETE'])]
    public function deleteWarranty(int $id, Request $request, EntityManagerInterface $em, DocumentService $documentService): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $warranty = $this->warrantyRepository->find($id);

            if (!$warranty) {
                return new JsonResponse("Warranty not found", Response::HTTP_NOT_FOUND);
            }

            // check if there are any warranties left associated to a manufacturer
            $manufacturerId = $warranty->getManufacturer()->getId();
            $manufacturerWarrantiesCount = $this->warrantyRepository->countWarrantiesByManufacturer($manufacturerId);

            if ($manufacturerWarrantiesCount <= 1) {
                $manufacturer = $this->manufacturerRepository->find($manufacturerId);
                $em->remove($manufacturer);
            }

            // Use the DocumentService to delete associated documents
            $documentService->deleteDocumentsByWarranty($warranty);

            $em->remove($warranty);
            $em->flush();

            return new JsonResponse("Warranty deleted successfully", Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}