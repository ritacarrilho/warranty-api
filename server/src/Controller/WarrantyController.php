<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Equipment;
use App\Entity\Manufacturer;
use App\Entity\Warranty;
use App\Repository\WarrantyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Middleware\JwtMiddleware;
use App\Repository\DocumentRepository;
use App\Repository\ManufacturerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WarrantyController extends AbstractController
{
    private $jwtMiddleware;
    private $warrantyRepository;
    private $documentRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, WarrantyRepository $warrantyRepository, DocumentRepository $documentRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->warrantyRepository = $warrantyRepository;
        $this->documentRepository = $documentRepository;
    }

    #[Route('/api/warranties', name: 'get_warranties', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            $warranties = $this->warrantyRepository->findOneByUser($userId);
            $data = [];
    
            foreach ($warranties as $warranty) {
                $startDate = $warranty->getStartDate();
                $endDate = $warranty->getEndDate();
    
                $manufacturerId = null;
                $manufacturer = $warranty->getManufacturer();
                if ($manufacturer !== null) {
                    $manufacturerId = $manufacturer->getId();
                }
    
                $data[] = [
                    'id' => $warranty->getId(),
                    'reference' => $warranty->getReference(),
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
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
    public function create(Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            $requestData = json_decode($request->getContent(), true);
    
            // Verify if manufacturer_id is present in the request data, else set it to null
            $manufacturerId = isset($requestData['manufacturer_id']) ? $requestData['manufacturer_id'] : null;
    
            $entityManager = $doctrine->getManager();
            $manufacturerRepository = $entityManager->getRepository(Manufacturer::class);
            $equipmentRepository = $entityManager->getRepository(Equipment::class);
    
            $equipment = $equipmentRepository->find($requestData['equipment_id']);
            
            // Only fetch manufacturer if manufacturer_id is in request
            $manufacturer = null;
            if ($manufacturerId !== null) {
                $manufacturer = $manufacturerRepository->find($manufacturerId);
            }
    
            $warranty = new Warranty();
            $warranty->setReference($requestData['reference'])
                ->setStartDate(new \DateTime($requestData['start_date']))
                ->setEndDate(new \DateTime($requestData['end_date']))
                ->setEquipment($equipment)
                ->setManufacturer($manufacturer);
    
            $entityManager->persist($warranty);
            $entityManager->flush();

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
                'message' => 'Equipment created successfully',
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
                throw new HttpException(Response::HTTP_BAD_REQUEST, "Bad request");
            }
    
            $warranty = $this->warrantyRepository->find($id);
    
            if (!$warranty) {
                return new JsonResponse("Warranty not found", Response::HTTP_NOT_FOUND);
            }
    
            $manufacturer = $warranty->getManufacturer();
            $documents = $this->documentRepository->findByWarranty($id);
    
            $documentObj = [];
    
            if ($documents !== null) {
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
                'start_date' => $warranty->getStartDate()->format('Y-m-d'),
                'end_date' => $warranty->getEndDate()->format('Y-m-d'),
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
    public function updateWarranty(int $id, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $warranty = $this->warrantyRepository->find($id);

            if (!$warranty) {
                throw new HttpException("Warranty not found", Response::HTTP_NOT_FOUND);
            }

            $requestData = json_decode($request->getContent(), true);

            // Update warranty fields
            if (isset($requestData['reference'])) {
                $warranty->setReference($requestData['reference']);
            }
            if (isset($requestData['start_date'])) {
                $warranty->setStartDate(new \DateTime($requestData['start_date']));
            }
            if (isset($requestData['end_date'])) {
                $warranty->setEndDate(new \DateTime($requestData['end_date']));
            }

            $em->flush();

            $warrantyData = [
                'id' => $warranty->getId(),
                'reference' => $warranty->getReference(),
                'start_date' => $warranty->getStartDate()->format('Y-m-d'),
                'end_date' => $warranty->getEndDate()->format('Y-m-d'),
                'equipment_id' => $warranty->getEquipment()->getId(),
                'manufacturer_id' => $warranty->getEquipment()->getId(),
            ];
    
            $serializedWarranty = $serializer->serialize($warrantyData, 'json');

            $responseData = [
                'message' => 'Equipment created successfully',
                'data' => json_decode($serializedWarranty, true)
            ];

            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/warranty/{id}', name:"delete_warranty", methods:['DELETE'])]
    public function deleteWarranty(int $id, Request $request, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $warranty = $this->warrantyRepository->find($id);

            if (!$warranty) {
                throw new HttpException("Warranty not found", Response::HTTP_NOT_FOUND);
            }

            $documents = $this->documentRepository->findByWarranty($id);

            if ($documents !== null) {
                foreach($documents as $document) {
                    $em->remove($document);
                }
            }

            $em->remove($warranty);
            $em->flush();

            return new JsonResponse("Warranty deleted successfully", Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}