<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use  Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CategoryController extends AbstractController
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     *  @Route("api/test/categories/{id}", name="get_one_category", methods={"GET"})
    */
    public function getDetailCategory(int $id, SerializerInterface $serializer, CategoryRepository $categoryRepository): JsonResponse {

        $category = $categoryRepository->find($id);
        if ($category) {
            $jsonCategory = $serializer->serialize($category, 'json');
            return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/test', name:"get_all_categories", methods: ['GET'])]
   
    public function getAll(): JsonResponse
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
}
