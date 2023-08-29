<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Manufacturer;
use App\Entity\Document;
use App\Repository\DocumentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class DocumentController extends AbstractController
{
    private $documentRepository;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    #[Route('/api/documents', name:"get_documents", methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            

        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred while fetching documents.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/document', name: "create_document", methods: ['POST'])]
    public function create(Request $request, PersistenceManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        try {
            $requestData = json_decode($request->getContent(), true);
    
            $uploadedFile = $request->files->get('file'); // 'file' should be the name attribute of the file input in your mobile app form
            $fileName = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
    
            $uploadedFile->move($this->getParameter('document_directory'), $fileName); // 'document_directory' is the parameter name where you configure the directory path in services.yaml
    
            $document = new Document();
            $document->setName($requestData['name'])
                ->setPath($fileName);
            
            // ... set other properties and relationships
    
            $entityManager = $doctrine->getManager();
            $entityManager->persist($document);
            $entityManager->flush();
    
            $jsonDocument = $serializer->serialize($document, 'json');
            return new JsonResponse($jsonDocument, Response::HTTP_CREATED, [], true);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred while creating the document.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('api/document/{id}', name:"get_document", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse 
    {
        try {
           
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while retrieving the document'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    #[Route('/api/document/{id}', name:"update_document", methods:['PUT'])]
    public function update(Request $request, SerializerInterface $serializer, Manufacturer $currentManufacturer, EntityManagerInterface $em): JsonResponse 
    {
        try {
            
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while updating the document'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/document/{id}', name: 'delete_document', methods: ['DELETE'])]
    public function delete(Document $document, EntityManagerInterface $em): JsonResponse 
    {
        try {
            // Get the file path from the document entity
            $filePath = $document->getPath();

            // Delete the physical file
            $filesystem = new Filesystem();
            $filesystem->remove($filePath);

            // Remove and flush the document entity from the database
            $em->remove($document);
            $em->flush();

            return new JsonResponse("Document deleted", Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while deleting the document'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

