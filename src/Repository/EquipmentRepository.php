<?php

namespace App\Repository;

use App\Entity\Equipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipment>
 *
 * @method Equipment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipment[]    findAll()
 * @method Equipment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

   public function findByUser($user): array
   {
       return $this->createQueryBuilder('e')
           ->andWhere('e.is_active = 1')
           ->andWhere('e.user = :user')
           ->setParameter('user', $user)
           ->getQuery()
           ->getResult();
   }

   public function findByHistoric($userId): array
   {
       return $this->createQueryBuilder('e')
           ->andWhere('e.is_active = 0')
           ->andWhere('e.user = :userId')
           ->setParameter('userId', $userId)
           ->getQuery()
           ->getResult();
   }

    public function findEquipmentById(int $equipmentId, int $userId): ?Equipment
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :equipmentId')
            ->andWhere('e.user = :userId')
            ->setParameter('equipmentId', $equipmentId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
