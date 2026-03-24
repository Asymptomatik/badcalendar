<?php

namespace App\Repository;

use App\Entity\Session;
use App\Enum\SessionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /** Retourne les prochaines séances à venir */
    public function findUpcoming(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.date >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** Retourne les séances sans responsable clés (récurrentes uniquement) */
    public function findWithoutResponsableKeys(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.type != :dimanche')
            ->andWhere('s.responsableKeys IS NULL')
            ->andWhere('s.date >= :now')
            ->setParameter('dimanche', SessionType::DIMANCHE)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Vérifie l'existence d'une séance par type et date */
    public function findOneByTypeAndDate(SessionType $type, \DateTimeImmutable $date): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.type = :type')
            ->andWhere('s.date = :date')
            ->setParameter('type', $type)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** Retourne les séances dans un intervalle de dates */
    public function findByDateRange(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Retourne les séances nécessitant un rappel (24h avant) */
    public function findSessionsForReminder(): array
    {
        $start = new \DateTimeImmutable('tomorrow midnight');
        $end = new \DateTimeImmutable('tomorrow 23:59:59');

        return $this->createQueryBuilder('s')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->andWhere('s.responsableKeys IS NOT NULL')
            ->andWhere('s.type != :dimanche')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('dimanche', SessionType::DIMANCHE)
            ->getQuery()
            ->getResult();
    }
}
