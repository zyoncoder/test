<?php

namespace App\Repository;

use App\Entity\Rent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rent>
 *
 * @method Rent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rent[]    findAll()
 * @method Rent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rent::class);
    }

    public function add(Rent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Rent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBetweenDates($userId, $fromDate, $toDate): int
    {
        return $this->createQueryBuilder('r')
            ->select('count(1)')
            ->where('r.user = :user_id')
            ->andWhere('r.created_at BETWEEN :from_date AND :to_date')
            ->andWhere('r.action_point_withdrew = 0')
            ->setParameter('user_id', $userId)
            ->setParameter('from_date', $fromDate)
            ->setParameter('to_date', $toDate)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}
