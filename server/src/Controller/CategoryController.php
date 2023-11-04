<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CategoryController extends AbstractController
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/api/categories', name:"get_categories", methods: ['GET'])]
    public function list(): JsonResponse
    {
        try{
            $categories = $this->categoryRepository->findAll();
            $data = [];
    
            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category->getId(),
                    'label' => $category->getLabel(),
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/category', name:"create_category", methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true);

            if (!isset($requestData['label']) || empty($requestData['label'])) {
                return new JsonResponse("Missing or empty 'label' field");
            }

            $category = new Category();
            $category->setLabel($requestData['label']);
    
            $em->persist($category);
            $em->flush();
    
            $jsonCategory = $serializer->serialize($category, 'json');
            $responseData = [
                'message' => 'Category created with success',
                'data' => json_decode($jsonCategory, true)
            ];
    
            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('api/category/{id}', name:"get_category", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $category = $this->categoryRepository->find($id);
    
            if ($category) {
                $jsonCategory = $serializer->serialize($category, 'json');
                return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
            }
    
            return new JsonResponse("Category not found", Response::HTTP_NOT_FOUND);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/category/{id}', name:"update_category", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Category $currentCategory, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $updatedCategory = $serializer->deserialize($request->getContent(), 
                Category::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]
            );

            $requestData = json_decode($request->getContent(), true);
            if (!isset($requestData['label']) || empty($requestData['label'])) {
                return new JsonResponse("Missing or empty 'label' field");
            }
    
            $em->flush();
    
            $jsonCategory = $serializer->serialize($updatedCategory, 'json');
            $responseData = [
                'message' => 'Category modified with success',
                'data' => json_decode($jsonCategory, true)
            ];
    
            return new JsonResponse($responseData, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/category/{id}', name: 'delete_category', methods: ['DELETE'])]
    public function delete(Category $category, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $em->remove($category);
            $em->flush();
    
            return new JsonResponse("Category deleted", Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}