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
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $manufacturers = $this->manufacturerRepository->findAll();
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
    }


    #[Route('/api/manufacturers', name:"create_manufacturer", methods: ['POST'])]
    public function create(Request $request, PersistenceManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $manufacturer = new Manufacturer();
        $manufacturer->setName($requestData['name'])
                ->setEmail($requestData['email'])
                ->setPhone($requestData['phone'])
                ->setAddress($requestData['address'])
                ->setZipCode($requestData['zip_code'])
                ->setCity($requestData['city'])
                ->setCountry($requestData['country']);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($manufacturer);
        $entityManager->flush();

        $jsonManufacturer = $serializer->serialize($manufacturer, 'json');
        return new JsonResponse($jsonManufacturer, Response::HTTP_CREATED, [], true);
    }


    #[Route('api/manufacturers/{id}', name:"get_manufacturer", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $manufacturer = $this->manufacturerRepository->find($id);

            if ($manufacturer) {
                $jsonManufacturer = $serializer->serialize($manufacturer, 'json');

                return new JsonResponse($jsonManufacturer, Response::HTTP_OK, [], true);
            }
        } catch (\Exception $ex) {
            throw new NotFoundHttpException();
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
    }

    
    #[Route('/api/manufacturers/{id}', name:"update_manufacturer", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Manufacturer $currentManufacturer, EntityManagerInterface $em): JsonResponse 
    {
        $updatedManufacturer = $serializer->deserialize($request->getContent(), 
            Manufacturer::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentManufacturer]
        );
        
        $em->persist($updatedManufacturer);
        $em->flush();

        $jsonManufacturer = $serializer->serialize($updatedManufacturer, 'json');
        return new JsonResponse($jsonManufacturer, Response::HTTP_OK, [], true);
   }


   #[Route('/api/manufacturers/{id}', name: 'delete_manufacturer', methods: ['DELETE'])]
   public function delete(Manufacturer $manufacturer, EntityManagerInterface $em): JsonResponse 
   {
       $em->remove($manufacturer);
       $em->flush();

       return new JsonResponse("Manufacturer deleted", Response::HTTP_OK);
   }
}
