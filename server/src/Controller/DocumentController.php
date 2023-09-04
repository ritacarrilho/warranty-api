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
use App\Service\DocumentService;
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

    #[Route('/api/documents', name: 'list_documents', methods: ['GET'])]
    public function list(Request $request): JsonResponse
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
    
            // Serialize documents and include image data
            $serializedDocuments = [];
            foreach ($documents as $document) {
                $imagePath = $this->getParameter('uploads_directory') . $document->getPath();
    
                if (!file_exists($imagePath)) {
                    continue;
                }
    
                $imageData = file_get_contents($imagePath);

                // Encode binary data as base64
                $imageData = base64_encode($imageData);
        
                // Construct the response data
                $responseData = [
                    'id' => $document->getId(),
                    'name' => $document->getName(),
                    'path' => $document->getPath(),
                    'imageData' => $imageData,
                ];
    
                $serializedDocuments[] = $responseData;
            }
    
            // Encode the array as a JSON string before passing it to JsonResponse
            $jsonData = json_encode($serializedDocuments);
    
            // Return the JSON response
            $response = new JsonResponse($jsonData, Response::HTTP_OK, [], true);
            $response->headers->set('Content-Type', 'application/json');
    
            return $response;
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

            // Check if the requested document belongs to the connected user
            $doc = $this->documentRepository->isDocumentBelongsToUser($document->getId(), $userId);

            if (!$doc) {
                return new JsonResponse('Bad request', Response::HTTP_BAD_REQUEST);
            }

            // Load the document again (not $doc)
            $document = $this->documentRepository->find($document->getId());

            // Construct the image path using the document's path property
            $imagePath = $this->getParameter('uploads_directory') . $document->getPath();

            // Check if the image file exists
            if (!file_exists($imagePath)) {
                return new JsonResponse('Image not found', Response::HTTP_NOT_FOUND);
            }

            // Read the image file into binary data
            $imageData = file_get_contents($imagePath);

            // Encode binary data as base64
            $imageData = base64_encode($imageData);

            // Construct the response data
            $responseData = [
                'id' => $document->getId(),
                'name' => $document->getName(),
                'path' => $document->getPath(),
                'imageData' => $imageData,
            ];

            // Create a JSON response with appropriate headers
            $response = new JsonResponse($responseData, Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/document', name: 'upload_document', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse {
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
            $document->setPath('/' . $newDocName);

            $entityManager->persist($document);
            $entityManager->flush();

            // Serialize the document including the image data
            $serializedDocument = $serializer->serialize($document, 'json', ['groups' => 'document']);
            $imagePath = $this->getParameter('uploads_directory') . '/' . $newDocName;
            $imageData = file_get_contents($imagePath);
            $imageData = base64_encode($imageData);

            $documentData = [
                'document' => $serializedDocument,
                'imageData' => $imageData,
            ];

            return new JsonResponse($documentData, Response::HTTP_CREATED);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }

    #[Route('/api/document/{id}', name: 'delete_document', methods: ['DELETE'])]
    public function deleteDocument(Request $request, Document $document, DocumentService $documentService): JsonResponse
    {
        try {
            $authToken = $request->headers->get('Authorization');
            $userId = $this->jwtMiddleware->getUserId();
    
            if (!$authToken || !$userId) {
                return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
            }
    
            // Delete the document using the service
            $documentService->deleteDocument($document);
    
            return new JsonResponse(['message' => 'Document deleted successfully'], Response::HTTP_OK);
        } catch (HttpException $exception) {
            return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
        } catch (HttpException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}



// #[Route('/api/document/{id}', name: 'delete_document', methods: ['DELETE'])]
// public function deleteDocument(Request $request, Document $document, EntityManagerInterface $entityManager): JsonResponse
// {
//     try {
//         $authToken = $request->headers->get('Authorization');
//         $userId = $this->jwtMiddleware->getUserId();

//         if (!$authToken || !$userId) {
//             return new JsonResponse("Bad request", Response::HTTP_BAD_REQUEST);
//         }

//         // Delete the document from the public/uploads/documents directory
//         $documentPath = $this->getParameter('uploads_directory') . '/' . $document->getName();
        
//         if (file_exists($documentPath)) {
//             unlink($documentPath);
//         }

//         // Remove document entry from the database
//         $entityManager->remove($document);
//         $entityManager->flush();

//         return new JsonResponse(['message' => 'Document deleted successfully'], Response::HTTP_OK);
//     } catch (HttpException $exception) {
//         return new JsonResponse("Wrong request", Response::HTTP_NOT_FOUND);
//     } catch (HttpException $exception) {
//         return new JsonResponse(['error' => $exception->getMessage()], $exception->getStatusCode());
//     }


// #[Route('/api/test', name: 'list_test', methods: ['GET'])]
// public function sendFilesAction()
// {
//     // Read image file into binary data
//     $imagePath = $this->getParameter('uploads_directory') . '/test.png'; // Replace with the actual path to your image
//     $imageData = file_get_contents($imagePath);

//     // Encode binary data as base64
//     $imageData = base64_encode($imageData);

//     // Create an array containing image and PDF data
//     $responseData = [
//         'imageData' => $imageData,
//     ];

//     // Create a JSON response with appropriate headers
//     $response = new JsonResponse($responseData, Response::HTTP_OK);
//     $response->headers->set('Content-Type', 'application/json');

//     return $response;
// }