<?php

namespace App\Repository;

use App\Entity\Hangout;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hangout>
 */
class HangoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hangout::class);
    }

    //methode pour gerer les filters et injections des données utilisateurs et du formulaire
    public function findFilteredEvent(?User $user, array $filters)
    {
        $qb = $this->createQueryBuilder('h');

        if ($user) {
            $qb->andWhere('h.organizer = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['campus'])) {
            $qb->andWhere('h.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('h.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['start'])) {
            $qb->andWhere('h.startingDateTime >= :start')
                ->setParameter('start', $filters['start']);
        }

        if (!empty($filters['end'])) {
            $qb->andWhere('h.startingDateTime <= :end')
                ->setParameter('end', $filters['end']);
        }

        if (!empty($filters['state'])) {
            $qb->andWhere('h.state = :state')
                ->setParameter('state', $filters['state']);
        }

        if ($isOrganizer) {
            $qb->andWhere('h.organizer = :user')
                ->setParameter('user', $user);
        }

        if ($isRegistered) {
            $qb->join('h.participants', 'p')
                ->andWhere('p = :user')
                ->setParameter('user', $user);
        }

        if ($isNotRegistered) {
            $qb->leftJoin('h.participants', 'p')
                ->andWhere('p != :user OR p IS NULL')
                ->setParameter('user', $user);
        }

        if ($isPast) {
            $qb->andWhere('h.startingDateTime < :today')
                ->setParameter('today', new \DateTime());
        }


        return $qb->getQuery()->getResult();

    }



    //    /**
    //     * @return Hangout[] Returns an array of Hangout objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Hangout
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
