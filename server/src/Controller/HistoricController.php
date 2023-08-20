<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use App\Middleware\JwtMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HistoricController extends AbstractController
{
    private $jwtMiddleware;
    private $equipmentRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, EquipmentRepository $equipmentRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->equipmentRepository = $equipmentRepository;
    }

    #[Route('/api/historic', name:"get_historic", methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $authToken = $request->headers->get('Authorization');
        $userId = $this->jwtMiddleware->getUserId();

        if($authToken && $userId) {
            $equipments = $this->equipmentRepository->findByHistoric($userId);
            $data = [];

            foreach ($equipments as $equipment) {
                $purchaseDate = $equipment->getPurchaseDate(); // format date

                $category = [
                    'id' => $equipment->getCategory()->getId(),
                    'label' => $equipment->getCategory()->getLabel(),
                ];

                $data[] = [
                    'id' => $equipment->getId(),
                    'name' => $equipment->getName(),
                    'brand' => $equipment->getBrand(),
                    'model' => $equipment->getModel(),
                    'picture' => $equipment->getPicture(),
                    'serial_code' => $equipment->getSerialCode(),
                    'purchase_date' => $purchaseDate->format('Y-m-d'),
                    'is_active' => $equipment->isIsActive(),
                    'category' => $category
                ];
            }
            return new JsonResponse($data, Response::HTTP_OK);
        }
        return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
    }
}