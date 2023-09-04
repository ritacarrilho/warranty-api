<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\Warranty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 *
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

   public function findByWarranty($warranty_id): array
   {
       return $this->createQueryBuilder('d')
           ->andWhere('d.warranty = :val')
           ->setParameter('val', $warranty_id)
           ->getQuery()
           ->getResult();
   }

   public function isDocumentBelongsToUser(int $document_id, int $user_id): ?Document
   {
        return $this->createQueryBuilder('d')
            ->join('d.warranty', 'w')
            ->join('w.equipment', 'e')
            ->where('e.user = :userId')
            ->andWhere('d.id = :documentId')
            ->setParameter('userId', $user_id)
            ->setParameter('documentId', $document_id)
            ->getQuery()
            ->getOneOrNullResult();
   }

   public function findByUser($user_id): array
   {
       return $this->createQueryBuilder('d')
                ->join('d.warranty', 'w')
                ->join('w.equipment', 'e')
                ->where('e.user = :userId')
                ->setParameter('userId', $user_id)
                ->getQuery()
                ->getResult();
   }

   public function findDocumentsByWarranty(Warranty $warranty)
   {
       return $this->createQueryBuilder('d')
           ->where('d.warranty = :warranty')
           ->setParameter('warranty', $warranty)
           ->getQuery()
           ->getResult();
   }

//    /**
//     * @return Document[] Returns an array of Document objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Document
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
