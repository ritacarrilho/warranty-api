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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CategoryController extends AbstractController
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('api/categories/{id}', name:"getCategory", methods:["GET"])]
    public function showCategory(int $id, SerializerInterface $serializer, CategoryRepository $categoryRepository): JsonResponse 
    {
        $category = $categoryRepository->find($id);
        if ($category) {
            $jsonCategory = $serializer->serialize($category, 'json');
            return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/categories/{id}', name:"updateCategory", methods:['PUT'])]
    public function updateCategory(Request $request, SerializerInterface $serializer, Category $currentCategory, EntityManagerInterface $em): JsonResponse 
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


   #[Route('/api/categories/{id}', name: 'deleteCategory', methods: ['DELETE'])]
   public function deleteCategory(Category $category, EntityManagerInterface $em): JsonResponse 
   {
       $em->remove($category);
       $em->flush();

       return new JsonResponse("Category deleted", Response::HTTP_OK);
   }

   #[Route('/api/categories', name:"getCategories", methods: ['GET'])]
   public function listCategories(): JsonResponse
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


    #[Route('/api/categories', name:"createCategory", methods: ['POST'])]
    public function createCategory(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $em->persist($category);
        $em->flush();

        $jsonCatgory = $serializer->serialize($category, 'json');
        // $location = $urlGenerator->generate('createCategory', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCatgory, Response::HTTP_OK, [], true);
   }
}