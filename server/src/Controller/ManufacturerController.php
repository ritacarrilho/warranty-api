<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Manufacturer;
use App\Repository\ManufacturerRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ManufacturerController extends AbstractController
{
    private $manufacturerRepository;

    public function __construct(ManufacturerRepository $manufacturerRepository)
    {
        $this->manufacturerRepository = $manufacturerRepository;
    }

    #[Route('/api/manufacturers', name:"get_manufacturers", methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $manufacturers = $this->manufacturerRepository->findAll();

            if(empty($manufacturers)) {
                return new JsonResponse("Manufacturers not found", Response::HTTP_NOT_FOUND);
            }

            $data = [];
    
            foreach ($manufacturers as $manufacturer) {
                $data[] = [
                    'id' => $manufacturer->getId(),
                    'name' => $manufacturer->getName(),
                    'email' => $manufacturer->getEmail(),
                    'phone' => $manufacturer->getPhone(),
                    'address' => $manufacturer->getAddress(),
                    'zip_code' => $manufacturer->getZipCode(),
                    'city' => $manufacturer->getCity(),
                    'country' => $manufacturer->getCountry(),
                ];
            }
    
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred while fetching manufacturers.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/manufacturer', name:"create_manufacturer", methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true);
    
            // if (!isset($requestData['name']) || empty($requestData['name']) ||
            //     !isset($requestData['email']) || empty($requestData['email']) ||
            //     !isset($requestData['phone']) || empty($requestData['phone']) ||
            //     !isset($requestData['address']) || empty($requestData['address']) ||
            //     !isset($requestData['zip_code']) || empty($requestData['zip_code']) ||
            //     !isset($requestData['city']) || empty($requestData['city']) ||
            //     !isset($requestData['country']) || empty($requestData['country'])
            // ) {
            //     return new JsonResponse(['error' => 'Invalid data. All fields are required.'], Response::HTTP_BAD_REQUEST);
            // }
    
            $manufacturer = new Manufacturer();
            $manufacturer->setName($requestData['name'])
                ->setEmail($requestData['email'])
                ->setPhone($requestData['phone'])
                ->setAddress($requestData['address'])
                ->setZipCode($requestData['zip_code'])
                ->setCity($requestData['city'])
                ->setCountry($requestData['country']);
    
            $em->persist($manufacturer);
            $em->flush();
    
            $jsonManufacturer = $serializer->serialize($manufacturer, 'json');
            return new JsonResponse($jsonManufacturer, Response::HTTP_CREATED, [], true);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred while creating the manufacturer.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('api/manufacturer/{id}', name:"get_manufacturer", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $manufacturer = $this->manufacturerRepository->find($id);
    
            if ($manufacturer) {
                $jsonManufacturer = $serializer->serialize($manufacturer, 'json');
                return new JsonResponse($jsonManufacturer, Response::HTTP_OK, [], true);
            }
    
            return new JsonResponse(['error' => 'Manufacturer not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while retrieving the manufacturer'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    #[Route('/api/manufacturer/{id}', name:"update_manufacturer", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Manufacturer $currentManufacturer, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $manufacturer = $this->manufacturerRepository->find($currentManufacturer);

            if(!$manufacturer){
                return new JsonResponse("Manufacturer not found", Response::HTTP_NOT_FOUND);
            }

            $updatedManufacturer = $serializer->deserialize($request->getContent(), 
                Manufacturer::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentManufacturer]
            );

            $em->flush();
    
            $jsonManufacturer = $serializer->serialize($updatedManufacturer, 'json');
            return new JsonResponse($jsonManufacturer, Response::HTTP_OK, [], true);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while updating the manufacturer'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/manufacturer/{id}', name: 'delete_manufacturer', methods: ['DELETE'])]
    public function delete(Manufacturer $manufacturer, EntityManagerInterface $em, Manufacturer $currentManufacturer): JsonResponse 
    {
        try {
            $manufacturer = $this->manufacturerRepository->find($currentManufacturer);
            if(!$manufacturer){
                return new JsonResponse("Manufacturer not found", Response::HTTP_NOT_FOUND);
            }

            $em->remove($manufacturer);
            $em->flush();
    
            return new JsonResponse("Manufacturer deleted", Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while deleting the manufacturer'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
