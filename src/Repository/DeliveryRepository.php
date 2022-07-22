<?php

namespace App\Repository;

use App\Entity\Delivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Delivery>
 *
 * @method Delivery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Delivery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Delivery[]    findAll()
 * @method Delivery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    public function add(Delivery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Delivery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findUserDeliveriesBetweenDatesForWhichPointsWereNotWithdrawn($userId, $fromDate, $toDate): array
    {

        return $this->createQueryBuilder('d')
            ->where('d.user = :user_id')
            ->andWhere('d.created_at BETWEEN :from_date AND :to_date')
            ->andWhere('d.action_point_withdrew = 0')
            ->andWhere('d.booster_point_withdrew = 0')
            ->setParameter('user_id', $userId)
            ->setParameter('from_date', $fromDate)
            ->setParameter('to_date', $toDate)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUserDeliveriesBetweenDatesForWhichPointsWereNotWithdrawnAndBoosterWasNotUsed($userId, $fromDate, $toDate, $excludeIds): array
    {

        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder
            ->select('d.id')
            ->where('d.user = :user_id')
            ->andWhere('d.created_at BETWEEN :from_date AND :to_date')
            ->andWhere('d.action_point_withdrew = 0')
            ->andWhere('d.booster_point_withdrew = 0');

        if (count($excludeIds) > 0) {

            $queryBuilder->andWhere($queryBuilder->expr()->notIn('d.id', $excludeIds));
        }

        return $queryBuilder->setParameter('user_id', $userId)
            ->setParameter('from_date', $fromDate)
            ->setParameter('to_date', $toDate)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

