<?php

namespace App\Repository;

use App\Entity\Warranty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<Warranty>
 *
 * @method Warranty|null find($id, $lockMode = null, $lockVersion = null)
 * @method Warranty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Warranty[]    findAll()
 * @method Warranty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarrantyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Warranty::class);
    }

    public function getWarrantiesForEquipmentAndUser(int $equipmentId, int $userId, EntityManagerInterface $entityManager): array
    {
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $query = $queryBuilder
            ->select('w')
            ->from(Warranty::class, 'w')
            ->join('w.equipment', 'e')
            ->join('e.user', 'u')
            ->where('e.id = :equipmentId')
            ->andWhere('u.id = :userId')
            ->setParameter('equipmentId', $equipmentId)
            ->setParameter('userId', $userId)
            ->getQuery();
        
        return $query->getResult();
    }

    public function findOneByEquipment($value): ?Warranty
    {
       return $this->createQueryBuilder('w')
           ->andWhere('w.equipment = :val')
           ->setParameter('val', $value)
           ->getQuery()
           ->getOneOrNullResult();
    }

//    /**
//     * @return Warranty[] Returns an array of Warranty objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Warranty
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
