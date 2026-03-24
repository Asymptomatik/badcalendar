<?php

namespace App\Service;

use App\Entity\Session;
use App\Enum\SessionType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de génération automatique des séances récurrentes (lun/mer/jeu).
 * Idempotent : vérifie l'existence avant insertion.
 */
class SessionSchedulerService
{
    /** Heure des séances récurrentes */
    private const SESSION_HOUR = 20;
    private const SESSION_MINUTE = 15;

    /** Correspondance entre le jour de la semaine ISO et le type de séance */
    private const DAY_TO_TYPE = [
        1 => SessionType::LUNDI,
        3 => SessionType::MERCREDI,
        4 => SessionType::JEUDI,
    ];

    /** Lieu par défaut des séances */
    private const DEFAULT_LOCATION = 'Gymnase BoisguiBad';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SessionRepository $sessionRepository,
    ) {}

    /**
     * Génère les séances récurrentes pour les N prochaines semaines.
     * Retourne le nombre de séances créées.
     */
    public function generateUpcomingSessions(int $weeks = 4): int
    {
        $created = 0;
        $now = new \DateTimeImmutable();
        $startDate = $now->modify('monday this week');

        for ($w = 0; $w < $weeks; $w++) {
            $weekStart = $startDate->modify(sprintf('+%d weeks', $w));

            foreach (self::DAY_TO_TYPE as $isoDay => $sessionType) {
                $sessionDate = $this->getDateForIsoDay($weekStart, $isoDay);
                $sessionDateTime = $sessionDate->setTime(self::SESSION_HOUR, self::SESSION_MINUTE);

                // Vérification idempotente
                $existing = $this->sessionRepository->findOneByTypeAndDate($sessionType, $sessionDateTime);
                if ($existing !== null) {
                    continue;
                }

                $session = new Session();
                $session->setType($sessionType);
                $session->setDate($sessionDateTime);
                $session->setLocation(self::DEFAULT_LOCATION);

                $this->em->persist($session);
                $created++;
            }
        }

        $this->em->flush();
        return $created;
    }

    /** Calcule la date d'un jour ISO de la semaine à partir du lundi */
    private function getDateForIsoDay(\DateTimeImmutable $mondayOfWeek, int $isoDay): \DateTimeImmutable
    {
        return $mondayOfWeek->modify(sprintf('+%d days', $isoDay - 1));
    }
}
