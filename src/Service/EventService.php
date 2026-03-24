<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\User;
use App\Enum\EventRegistrationStatus;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service métier pour la gestion des inscriptions aux événements.
 */
class EventService implements EventServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventRegistrationRepository $registrationRepository,
    ) {}

    /** Inscrit un membre à un événement */
    public function register(Event $event, User $user): void
    {
        $existing = $this->registrationRepository->findOneByEventAndUser($event, $user);

        if ($existing !== null) {
            // Réactivation si désisté
            if ($existing->getStatus() === EventRegistrationStatus::DESISTE) {
                $existing->setStatus(EventRegistrationStatus::INSCRIT);
                $existing->setConfirmedAt(null);
                $this->em->flush();
            }
            return;
        }

        $registration = new EventRegistration();
        $registration->setEvent($event);
        $registration->setUser($user);
        $registration->setStatus(EventRegistrationStatus::INSCRIT);

        $this->em->persist($registration);
        $this->em->flush();
    }

    /** Désiste un membre d'un événement */
    public function desist(Event $event, User $user): void
    {
        $registration = $this->registrationRepository->findOneByEventAndUser($event, $user);
        if ($registration === null) {
            return;
        }

        $registration->setStatus(EventRegistrationStatus::DESISTE);
        $this->em->flush();
    }

    /** Confirme la participation d'un membre à un événement */
    public function confirm(Event $event, User $user): void
    {
        $registration = $this->registrationRepository->findOneByEventAndUser($event, $user);
        if ($registration === null || $registration->getStatus() !== EventRegistrationStatus::INSCRIT) {
            return;
        }

        $registration->setConfirmedAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
