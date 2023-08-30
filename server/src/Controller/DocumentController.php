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
use Symfony\Component\Validator\Validator\ValidatorInterface;

//TODO: file path unique

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
            $documents = $this->documentRepository->findAll();
            $data = [];

            foreach ($documents as $document) {
                $data[] = [
                    'id' => $document->getId(),
                    'name' => $document->getName(),
                    'path' => $document->getPath(),
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred while fetching documents.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/document', name:"create_document", methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        try {
            $requestData = json_decode($request->request->get('data'), true);

            $uploadedFile = $request->files->get('file');
            $fileDirectory = 'path_to_your_upload_directory'; // Configure this
            $fileName = $this->generateUniqueFileName().'.'.$uploadedFile->getClientOriginalExtension();

            $uploadedFile->move($fileDirectory, $fileName);

            $document = new Document();
            $document->setName($requestData['name'])
                ->setPath($fileDirectory.'/'.$fileName); // Save the full path

            // Validate the document entity
            $errors = $validator->validate($document);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }
            
            $em->persist($document);
            $em->flush();

            return new JsonResponse("Document created successfully", Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred while creating the document.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('api/document/{id}', name:"get_document", methods:["GET"])]
    public function show(int $id, SerializerInterface $serializer): JsonResponse 
    {
        try {
            $document = $this->documentRepository->find($id);

            if ($document) {
                $documentData = [
                    'id' => $document->getId(),
                    'name' => $document->getName(),
                    'path' => $document->getPath(),
                ];

                return new JsonResponse($documentData, Response::HTTP_OK);
            }

            return new JsonResponse(['error' => 'Document not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while retrieving the document'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/document/{id}', name:"update_document", methods:['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, DocumentRepository $documentRepository, ValidatorInterface $validator): JsonResponse 
    {
        try {
            $document = $documentRepository->find($id);

            if (!$document) {
                return new JsonResponse(['error' => 'Document not found'], Response::HTTP_NOT_FOUND);
            }

            $requestData = json_decode($request->getContent(), true);
            
            // Update document fields
            $document->setName($requestData['name']);

            $em->flush();

            return new JsonResponse("Document updated successfully", Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while updating the document'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/document/{id}', name: 'delete_document', methods: ['DELETE'])]
    public function delete(int $id, DocumentRepository $documentRepository, EntityManagerInterface $em): JsonResponse 
    {
        try {
            $document = $documentRepository->find($id);

            if (!$document) {
                return new JsonResponse(['error' => 'Document not found'], Response::HTTP_NOT_FOUND);
            }

            // Delete the file from storage
            $path = $document->getPath();
            if (file_exists($path)) {
                unlink($path);
            }

            $em->remove($document);
            $em->flush();

            return new JsonResponse("Document deleted successfully", Response::HTTP_OK);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => 'An error occurred while deleting the document'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Utility function to generate unique file names
    private function generateUniqueFileName(): string
    {
        return md5(uniqid());
    }
}