<?php

namespace App\Repository;

use App\Entity\Rideshare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rideshare>
 *
 * @method Rideshare|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rideshare|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rideshare[]    findAll()
 * @method Rideshare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RideshareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rideshare::class);
    }

    public function add(Rideshare $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Rideshare $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUserRidesharesBetweenDatesForWhichPointsWereNotWidthdrawn($userId, $fromDate, $toDate): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.user  = :user_id')
            ->andWhere('r.created_at BETWEEN :from_date AND :to_date')
            ->andWhere('r.action_point_withdrew = 0')
            ->andWhere('r.booster_point_withdrew = 0')
            ->setParameter('user_id', $userId)
            ->setParameter('from_date', $fromDate)
            ->setParameter('to_date', $toDate)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
