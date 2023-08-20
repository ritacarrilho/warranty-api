<?php

namespace App\Controller;

use App\Entity\Warranty;
use App\Repository\WarrantyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Middleware\JwtMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\EquipmentRepository;
use App\Repository\ManufacturerRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


class WarrantyController extends AbstractController
{
    private $jwtMiddleware;
    private $warrantyRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, WarrantyRepository $warrantyRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->warrantyRepository = $warrantyRepository;
    }

    #[Route('/api/warranties/equipment/{id}', name: 'get_warranties', methods: ['GET'])]
    public function list(int $id, Request $request, WarrantyRepository $warrantyRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();
    
        if ($authToken && $userId) {
            $requestData = json_decode($request->getContent(), true);

            $warranties = $warrantyRepository->getWarrantiesForEquipmentAndUser($id, $userId, $entityManager);
    
            $data = [];
    
            foreach ($warranties as $warranty) {
                $manufacturer = $warranty->getManufacturer();

                if ($manufacturer) {
                    $manufacturerData = [
                        'id' => $warranty->getManufacturer()->getId(),
                    ];
                }
                else {
                    $manufacturerData = null;
                }

                $data[] = [
                    'id' => $warranty->getId(),
                    'reference' => $warranty->getReference(),
                    'start_date' => $warranty->getStartDate()->format('Y-m-d'),
                    'end_date' => $warranty->getEndDate()->format('Y-m-d'),
                    'manufacturer' => $manufacturerData,
                ];
            }
    
            return new JsonResponse($data, Response::HTTP_OK);
        }
    
        return new JsonResponse("Unauthorized", Response::HTTP_UNAUTHORIZED);
    }


//     #[Route('/api/warranty', name: 'create_warranty', methods: ['POST'])]
//     public function createWarranty(Request $request, ManagerRegistry $doctrine, EquipmentRepository $equipmentRepository, ManufacturerRepository $manufacturerRepository, SerializerInterface $serializer, ValidatorInterface $validator, Security $security): JsonResponse 
//     {
//     $authToken = $request->headers->get('Authorization');
//     $userId = $this->jwtMiddleware->getUserId();

//     if ($authToken && $userId) {
//         $requestData = json_decode($request->getContent(), true);

//         $entityManager = $doctrine->getManager();

//         // Fetch the associated equipment and manufacturer
//         $equipment = $equipmentRepository->findOneBy(['id' => $requestData['equipment'], 'user' => $security->getUser()]);
//         $manufacturer = $manufacturerRepository->findOneBy(['id' => $requestData['manufacturer']]);

//         if (!$equipment || !$manufacturer) {
//             return new JsonResponse("Invalid equipment or manufacturer", Response::HTTP_BAD_REQUEST);
//         }

//         // Create a new Warranty instance and set its properties
//         $warranty = new Warranty();
//         $warranty->setReference($requestData['reference'])
//             ->setStartDate(new \DateTime($requestData['start_date']))
//             ->setEndDate(new \DateTime($requestData['end_date']))
//             ->setEquipment($equipment)
//             ->setManufacturer($manufacturer);

//         // Validate the Warranty entity
//         $errors = $validator->validate($warranty);

//         if (count($errors) > 0) {
//             return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
//         }

//         // Persist and flush the Warranty entity
//         $entityManager->persist($warranty);
//         $entityManager->flush();

//         // Serialize the created warranty and return the response
//         $jsonWarranty = $serializer->serialize($warranty, 'json');
//         return new JsonResponse($jsonWarranty, Response::HTTP_CREATED, [], true);
//     }

//     return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
// }


    #[Route('/api/warranty/{id}', name:"get_warranty", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer, Request $request): JsonResponse {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if ($authToken && $userId) {
            $warranty = $this->warrantyRepository->find($id);

            if ($warranty) {
                $manufacturer = $warranty->getManufacturer();
                TODO: // add document

                if ($manufacturer) {
                    $manufacturerData = [
                        'id' => $warranty->getManufacturer()->getId(),
                        'name' => $warranty->getManufacturer()->getName(),
                        'email' => $warranty->getManufacturer()->getEmail(),
                        'phone' => $warranty->getManufacturer()->getPhone(),
                        'address' => $warranty->getManufacturer()->getAddress(),
                        'zip_code' => $warranty->getManufacturer()->getZipCode(),
                        'city' => $warranty->getManufacturer()->getCity(),
                        'country' => $warranty->getManufacturer()->getCountry(),
                    ];
                }
                else {
                    $manufacturerData = null;
                }
                $warrantyObj = [
                    'id' => $warranty->getId(),
                    'reference' => $warranty->getReference(),
                    'start_date' => $warranty->getStartDate()->format('Y-m-d'),
                    'end_date' => $warranty->getEndDate()->format('Y-m-d'),
                    'manufacturer' => $manufacturerData,
                ];

                $jsonWarranty = $serializer->serialize($warrantyObj, 'json');
                return new JsonResponse($jsonWarranty, Response::HTTP_OK, [], true);
            }

            return new JsonResponse("Warranty not found", Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    


}