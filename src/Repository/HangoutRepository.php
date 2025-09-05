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

//        if ($user) {
//            $qb->andWhere('h.organizer = :user')
//                ->setParameter('user', $user);
//        }

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

        $userConditions = [];

        // Sorties dont je suis organisateur
        if (!empty($filters['isOrganizer'])) {
            $userConditions[] = 'h.organizer = :user';
        }

        // Sorties auxquelles je suis inscrit
        if (!empty($filters['isRegistered'])) {
            $qb->leftJoin('h.subscriberLst', 'subscribers');
            $userConditions[] = 'subscribers = :user';
        }

        // Sorties auxquelles je ne suis pas inscrit (et dont je ne suis pas organisateur)
        if (!empty($filters['isNotRegistered'])) {
            $qb->leftJoin('h.subscriberLst', 'notSubscribers');
            $userConditions[] = '(notSubscribers IS NULL OR notSubscribers != :user) AND h.organizer != :user';
        }

        // Appliquer les conditions utilisateur avec OR
        if (!empty($userConditions)) {
            $qb->andWhere('(' . implode(' OR ', $userConditions) . ')')
                ->setParameter('user', $user);
        }

        // Gestion des sorties passées/futures
        if (!empty($filters['isPast'])) {
            $qb->andWhere('h.startingDateTime < :now')
                ->setParameter('now', new \DateTime());
        } else {
            // Par défaut, seulement les sorties futures
            $qb->andWhere('h.startingDateTime >= :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();

    }

}
