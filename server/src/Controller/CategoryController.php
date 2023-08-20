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
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
        $categories = $this->categoryRepository->findAll();
        $data = [];
 
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->getId(),
                'label' => $category->getLabel(),
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/categories', name:"create_category", methods: ['POST'])]
    public function create(Request $request, PersistenceManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        $category = new Category();
        $category->setLabel($requestData['label']);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        $jsonCategory = $serializer->serialize($category, 'json');
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }


    #[Route('api/categories/{id}', name:"get_category", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer, CategoryRepository $categoryRepository): JsonResponse 
    {
        $category = $categoryRepository->find($id);

        if ($category) {
            $jsonCategory = $serializer->serialize($category, 'json');
            return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/categories/{id}', name:"update_category", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Category $currentCategory, EntityManagerInterface $em): JsonResponse 
    {
        $updatedCategory = $serializer->deserialize($request->getContent(), 
            Category::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]
        );
        
        $em->persist($updatedCategory);
        $em->flush();

        $jsonCategory = $serializer->serialize($updatedCategory, 'json');
        return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
   }


   #[Route('/api/categories/{id}', name: 'delete_category', methods: ['DELETE'])]
   public function delete(Category $category, EntityManagerInterface $em): JsonResponse 
   {
       $em->remove($category);
       $em->flush();

       return new JsonResponse("Category deleted", Response::HTTP_OK);
   }
}