<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\User;
use App\Enum\EventRegistrationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventRegistration>
 */
class EventRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }

    /** Retourne l'inscription d'un user pour un événement */
    public function findOneByEventAndUser(Event $event, User $user): ?EventRegistration
    {
        return $this->createQueryBuilder('er')
            ->andWhere('er.event = :event')
            ->andWhere('er.user = :user')
            ->setParameter('event', $event)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** Retourne les inscriptions en attente de validation */
    public function findPendingValidation(): array
    {
        return $this->createQueryBuilder('er')
            ->andWhere('er.status = :status')
            ->setParameter('status', EventRegistrationStatus::EN_ATTENTE_VALIDATION)
            ->orderBy('er.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** Retourne les inscriptions non confirmées pour un événement */
    public function findUnconfirmedByEvent(Event $event): array
    {
        return $this->createQueryBuilder('er')
            ->andWhere('er.event = :event')
            ->andWhere('er.status = :status')
            ->andWhere('er.confirmedAt IS NULL')
            ->setParameter('event', $event)
            ->setParameter('status', EventRegistrationStatus::INSCRIT)
            ->getQuery()
            ->getResult();
    }
}
