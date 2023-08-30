<?php

namespace App\Controller;

use App\Repository\EquipmentRepository;
use App\Middleware\JwtMiddleware;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HistoricController extends AbstractController
{
    private $jwtMiddleware;
    private $equipmentRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, EquipmentRepository $equipmentRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->equipmentRepository = $equipmentRepository;
    }

    #[Route('/api/historic', name: "get_historic", methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            $equipments = $this->equipmentRepository->findByHistoric($userId);
            if(!$equipments) {
                return new JsonResponse("Equipments not found in historic", Response::HTTP_NOT_FOUND);
            }

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
                    'category_id' => $category['id'],
                    // 'category' => $category
                ];
            }
    
            return new JsonResponse($data, Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }


    #[Route('/api/historic/equipment/{id}', name: "add_to_historic", methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            $equipment = $this->equipmentRepository->find($id);
            if (!$equipment) {
                return new JsonResponse("Equipment not found", Response::HTTP_NOT_FOUND);
            }
    
            $requestData = json_decode($request->getContent(), true);
            
            $equipment->setName($requestData['name'])
                ->setBrand($requestData['brand'])
                ->setModel($requestData['model'])
                ->setSerialCode($requestData['serial_code'])
                ->setPurchaseDate(new \DateTime($requestData['purchase_date']))
                ->setIsActive('false');
            
            $em->flush();
    
            return new JsonResponse("Equipment successfully added to historic", Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}