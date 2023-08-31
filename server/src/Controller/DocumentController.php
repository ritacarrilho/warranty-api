<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Document;
use App\Repository\DocumentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Middleware\JwtMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;


class DocumentController extends AbstractController
{

    private $jwtMiddleware;
    private $documentRepository;

    public function __construct(JwtMiddleware $jwtMiddleware, DocumentRepository $documentRepository)
    {
        $this->jwtMiddleware = $jwtMiddleware;
        $this->documentRepository = $documentRepository;
    }

    #[Route('/api/documents', name: 'list_document', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $documents = $this->documentRepository->findByUser($userId);

            if (!$documents) {
                return new JsonResponse("Documents not found", Response::HTTP_NOT_FOUND);
            }
            
            if(!empty($documents)){
                $serializedDocuments = $serializer->serialize($documents, 'json', ['groups' => 'document']);
            
                return new JsonResponse($serializedDocuments, Response::HTTP_OK, [], true);
            }
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/document/{id}', name: 'get_document', methods: ['GET'])]
    public function show(Request $request, Document $document, SerializerInterface $serializer): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
            // check if requested doc belongs to user connected
            $doc = $this->documentRepository->isDocumentBelongsToUser($document->getId(), $userId);

            if(!$doc) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }

            $serializedDocument = $serializer->serialize($document, 'json', ['groups' => 'document']);
            return new JsonResponse($serializedDocument, Response::HTTP_OK, [], true);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/document', name: 'upload_document', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
            
            $uploadedFile = $request->files->get('path');

            if (!$uploadedFile) {
                return new JsonResponse('No document uploaded', Response::HTTP_BAD_REQUEST);
            }

            // Generate a unique document name
            $originalDocName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newDocName = $originalDocName . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            // Move the uploaded document to the public/uploads/documents directory
            $uploadedFile->move($this->getParameter('uploads_directory'), $newDocName);

            // Save document information to the database
            $document = new Document();
            $document->setName($newDocName);
            $document->setPath('/uploads/documents/' . $newDocName);
            $entityManager->persist($document);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Document uploaded successfully'], Response::HTTP_CREATED);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/document/{id}', name: 'delete_document', methods: ['DELETE'])]
    public function deleteDocument(Request $request, Document $document, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }

            // Delete the document from the public/uploads/documents directory
            $documentPath = $this->getParameter('uploads_directory') . '/' . $document->getName();
            
            if (file_exists($documentPath)) {
                unlink($documentPath);
            }

            // Remove document entry from the database
            $entityManager->remove($document);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Document deleted successfully'], Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}