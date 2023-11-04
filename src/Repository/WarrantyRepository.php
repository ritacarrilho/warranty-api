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

    public function getWarrantiesForEquipmentAndUser(int $equipmentId, int $userId): array
    {
        return $this->createQueryBuilder('w')
            ->join('w.equipment', 'e')
            ->join('e.user', 'u')
            ->where('e.id = :equipmentId')
            ->andWhere('u.id = :userId')
            ->setParameter('equipmentId', $equipmentId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findOneByEquipment($value): ?Warranty
    {
       return $this->createQueryBuilder('w')
           ->andWhere('w.equipment = :val')
           ->setParameter('val', $value)
           ->getQuery()
           ->getOneOrNullResult();
    }

    public function findOneByUser($userId): array
    {
       return $this->createQueryBuilder('w')
       ->join('w.equipment', 'e')
       ->join('e.user', 'u')
       ->where('e.id = w.equipment')
       ->andWhere('u.id = :userId')
       ->setParameter('userId', $userId)
       ->getQuery()
       ->getResult();
    }

    public function countWarrantiesByManufacturer($manufacturerId)
    {
        return $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.manufacturer = :manufacturerId')
            ->setParameter('manufacturerId', $manufacturerId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getManufacturerIdForWarranty($warrantyId)
    {
        return $this->createQueryBuilder('w')
            ->select('w.manufacturer')
            ->where('w.id = :warrantyId')
            ->setParameter('warrantyId', $warrantyId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
