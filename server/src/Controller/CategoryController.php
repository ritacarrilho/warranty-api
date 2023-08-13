<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     *  @Route("/categories/{id}", name="get_one_category", methods={"GET"})
    */
    public function get($id): JsonResponse
    {
        $customer = $this->categoryRepository->findOneBy(['id' => $id]);
    
        $data = [
            'id' => $customer->getId(),
            'label' => $customer->getLabel()
        ];
    
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/categories", name="get_all_customers", methods={"GET"})
     */
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
