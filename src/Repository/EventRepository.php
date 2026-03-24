<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /** Retourne les événements à venir */
    public function findUpcoming(int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.date >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('e.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** Retourne les événements nécessitant un rappel J-14 */
    public function findForReminderJ14(): array
    {
        $targetDate = new \DateTimeImmutable('+14 days midnight');
        $end = new \DateTimeImmutable('+14 days 23:59:59');

        return $this->createQueryBuilder('e')
            ->andWhere('e.date BETWEEN :start AND :end')
            ->setParameter('start', $targetDate)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    /** Retourne les événements nécessitant un rappel J-3 */
    public function findForReminderJ3(): array
    {
        $targetDate = new \DateTimeImmutable('+3 days midnight');
        $end = new \DateTimeImmutable('+3 days 23:59:59');

        return $this->createQueryBuilder('e')
            ->andWhere('e.date BETWEEN :start AND :end')
            ->setParameter('start', $targetDate)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}
