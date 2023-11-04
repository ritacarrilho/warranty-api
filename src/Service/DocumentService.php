<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Warranty;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DocumentService
{
    private $entityManager;
    private $uploadsDirectory;
    private $documentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        $uploadsDirectory,
        DocumentRepository $documentRepository
    ) {
        $this->entityManager = $entityManager;
        $this->uploadsDirectory = $uploadsDirectory;
        $this->documentRepository = $documentRepository;
    }


    public function deleteDocument(Document $document): void
    {
        $documentPath = $this->uploadsDirectory . $document->getPath();

        if (file_exists($documentPath)) {
            unlink($documentPath);
        }

        $this->entityManager->remove($document);
        $this->entityManager->flush();
    }


    public function deleteDocumentsByWarranty(Warranty $warranty)
    {
        try {
            $documents = $this->documentRepository->findDocumentsByWarranty($warranty);

            if($documents) {
                foreach ($documents as $document) {
                    $documentPath = $this->uploadsDirectory . '/' . $document->getPath();
    
                    if (file_exists($documentPath)) {
                        unlink($documentPath);
                    }
    
                    $this->entityManager->remove($document);
                }
    
                $this->entityManager->flush();
            }

        } catch (\Exception $exception) {
            throw new HttpException(500, 'Error deleting associated documents');
        }
    }
}