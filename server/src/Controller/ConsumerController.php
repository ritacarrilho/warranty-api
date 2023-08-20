<?php

namespace App\Controller;

use App\Entity\Consumer;
use App\Repository\ConsumerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Middleware\JwtMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\Persistence\ManagerRegistry;

class ConsumerController extends AbstractController
{
    private $jwtMiddleware;
    private $consumerRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, ConsumerRepository $consumerRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->consumerRepository = $consumerRepository;
    }

    #[Route('/api/consumers', name:"get_consumers", methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if($authToken && $userId) {
            $consumers = $this->consumerRepository->findAll();
            $data = [];

            foreach ($consumers as $consumer) {
                $user = [
                    'id' => $consumer->getUser()->getId(),
                    'email' => $consumer->getUser()->getEmail(),
                ];

                $data[] = [
                    'id' => $consumer->getId(),
                    'first_name' => $consumer->getFirstName(),
                    'last_name' => $consumer->getLastName(),
                    'phone' => $consumer->getPhone(),
                    'user' => $user
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        }
        return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/consumer', name:"get_consumer", methods:["GET"])]
    public function show(SerializerInterface $serializer, Request $request): JsonResponse {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if ($authToken && $userId) {
            $consumer = $this->consumerRepository->findByUser($userId);

            if ($consumer) {
                $consumerObj = [
                    'id' => $consumer->getId(),
                    'first_name' => $consumer->getFirstName(),
                    'last_name' => $consumer->getLastName(),
                    'phone' => $consumer->getPhone(),
                    'user' => [
                        'id' => $consumer->getUser()->getId(),
                        'email' => $consumer->getUser()->getEmail(),
                        // Include other user properties you need
                    ]
                ];

                $jsonConsumer = $serializer->serialize($consumerObj, 'json');
                return new JsonResponse($jsonConsumer, Response::HTTP_OK, [], true);
            }

            return new JsonResponse("Consumer not found", Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/consumer', name:"update_consumer", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse 
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if ($authToken && $userId) {
            $consumer = $this->consumerRepository->findByUser($userId);

            $updatedEquipment = $serializer->deserialize($request->getContent(), 
                Consumer::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $consumer]
            );
            
            $em->persist($updatedEquipment);
            $em->flush();

            return new JsonResponse("Consumer details successfully modified", Response::HTTP_OK, [], true);
        }
        return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
    }
}