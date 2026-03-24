<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\SessionRegistration;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SessionRegistration>
 */
class SessionRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionRegistration::class);
    }

    /** Retourne l'inscription d'un user pour une session */
    public function findOneBySessionAndUser(Session $session, User $user): ?SessionRegistration
    {
        return $this->createQueryBuilder('sr')
            ->andWhere('sr.session = :session')
            ->andWhere('sr.user = :user')
            ->setParameter('session', $session)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** Retourne les prochaines inscriptions d'un membre */
    public function findUpcomingByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('sr')
            ->join('sr.session', 's')
            ->andWhere('sr.user = :user')
            ->andWhere('s.date >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
