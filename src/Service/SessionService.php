<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\SessionRegistration;
use App\Entity\User;
use App\Enum\MemberType;
use App\Enum\SessionType;
use App\Repository\SessionRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service métier pour la gestion des séances de badminton.
 */
class SessionService implements SessionServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SessionRegistrationRepository $registrationRepository,
    ) {}

    public function filterAccessibleSessions(array $sessions, User $user): array
    {
        return array_values(array_filter(
            $sessions,
            fn (Session $session): bool => $this->canAccess($session, $user)
        ));
    }

    /** Inscrit un membre à une séance */
    public function register(Session $session, User $user): void
    {
        if (!$this->canAccess($session, $user)) {
            throw new AccessDeniedException('Vous n\'avez pas accès à cette séance.');
        }

        $existing = $this->registrationRepository->findOneBySessionAndUser($session, $user);
        if ($existing !== null) {
            return;
        }

        $registration = new SessionRegistration();
        $registration->setSession($session);
        $registration->setUser($user);
        $registration->setPresent(true);

        $this->em->persist($registration);
        $this->em->flush();
    }

    /** Désinscrit un membre d'une séance */
    public function unregister(Session $session, User $user): void
    {
        $registration = $this->registrationRepository->findOneBySessionAndUser($session, $user);
        if ($registration === null) {
            return;
        }

        $this->em->remove($registration);
        $this->em->flush();
    }

    /**
     * Vérifie si un membre peut accéder à une séance.
     * LOISIR : lundi, jeudi, dimanche
     * COMPETITEUR : lundi, mercredi, jeudi, dimanche
     */
    public function canAccess(Session $session, User $user): bool
    {
        $memberType = $user->getMemberType();
        if ($memberType === null) {
            return false;
        }

        return match($session->getType()) {
            SessionType::LUNDI, SessionType::JEUDI, SessionType::DIMANCHE => true,
            SessionType::MERCREDI => $memberType === MemberType::COMPETITEUR,
        };
    }

    /** Assigne le responsable clés d'une séance */
    public function assignResponsableKeys(Session $session, User $user): void
    {
        $session->setResponsableKeys($user);
        $this->em->persist($session);
        $this->em->flush();
    }
}
