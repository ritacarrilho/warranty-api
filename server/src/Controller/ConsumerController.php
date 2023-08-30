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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConsumerController extends AbstractController
{
    private $jwtMiddleware;
    private $consumerRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, ConsumerRepository $consumerRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->consumerRepository = $consumerRepository;
    }

    // route admin
    #[Route('/api/consumers', name:"get_consumers", methods: ['GET'])]
    public function list(Request $request, ConsumerRepository $consumerRepository): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            // check user and token
            if (!$authToken || !$userId) {
                throw new HttpException("Unauthorized", Response::HTTP_UNAUTHORIZED);
            }
    
            $consumers = $consumerRepository->findAll();
            $data = [];
    
            foreach ($consumers as $consumer) {
                $user = [
                    'id' => $consumer->getUser()->getId(),
                    'email' => $consumer->getUser()->getEmail(),
                ];
                // response object
                $data[] = [
                    'id' => $consumer->getId(),
                    'first_name' => $consumer->getFirstName(),
                    'last_name' => $consumer->getLastName(),
                    'phone' => $consumer->getPhone(),
                    'email' => $user["email"],
                    'user_id' => $user["id"],
                    // 'user' => $user
                ];
            }
    
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/consumer', name: 'get_consumer', methods: ['GET'])]
    public function show(SerializerInterface $serializer, ConsumerRepository $consumerRepository, Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            // check user and token
            if (!$authToken || !$userId) {
                throw new HttpException("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $consumer = $consumerRepository->findByUser($userId);
            // check if consumer exists
            if (!$consumer) {
                throw new HttpException("Consumer not found", Response::HTTP_NOT_FOUND);
            }

            // response object
            $consumerObj = [
                'id' => $consumer->getId(),
                'first_name' => $consumer->getFirstName(),
                'last_name' => $consumer->getLastName(),
                'phone' => $consumer->getPhone(),
                'email' => $consumer->getUser()->getEmail(),
                'user_id' => $consumer->getUser()->getId(),
                // 'user' => [
                //     'id' => $consumer->getUser()->getId(),
                //     'email' => $consumer->getUser()->getEmail(),
                // ]
            ];

            $jsonConsumer = $serializer->serialize($consumerObj, 'json');

            return new JsonResponse($jsonConsumer, Response::HTTP_OK, [], true);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/consumer', name: 'update_consumer', methods: ['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();

            // check user and token
            if (!$authToken || !$userId) {
                throw new BadRequestHttpException("Bad request");
            }
    
            $consumer = $this->consumerRepository->findByUser($userId);
            // check if consumer exists
            if (!$consumer) {
                throw new NotFoundHttpException("Consumer not found");
            }
    
            $updatedConsumer = $serializer->deserialize(
                $request->getContent(),
                Consumer::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $consumer]
            );
    
            $em->flush();
    
            $jsonUpdatedConsumer = $serializer->serialize($updatedConsumer, 'json', [
                'groups' => ['consumer_response']
            ]);
    
            $responseData = [
                'message' => 'Consumer details successfully modified',
                'data' => json_decode($jsonUpdatedConsumer, true)
            ];
    
            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (BadRequestHttpException $badRequestException) {
            return new JsonResponse(['error' => $badRequestException->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (NotFoundHttpException $notFoundException) {
            return new JsonResponse(['error' => $notFoundException->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}