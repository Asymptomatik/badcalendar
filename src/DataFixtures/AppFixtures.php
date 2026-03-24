<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\LostItem;
use App\Entity\Session;
use App\Entity\SessionRegistration;
use App\Entity\User;
use App\Enum\EventRegistrationStatus;
use App\Enum\LostItemStatus;
use App\Enum\MemberType;
use App\Enum\SessionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures de démonstration pour BoisguiBad Club Manager.
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ---- Création de l'admin ----
        $admin = new User();
        $admin->setEmail('admin@boisguibad.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('Bureau');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_MEMBER']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin@BoisguiBad2026!'));
        $admin->setIsVerified(true);
        $admin->setMemberType(MemberType::COMPETITEUR);
        $manager->persist($admin);

        // ---- Membres LOISIR ----
        $loisirMembers = [];
        $loisirData = [
            ['Pierre', 'Dupont', 'pierre@example.fr'],
            ['Marie', 'Martin', 'marie@example.fr'],
            ['Jean', 'Bernard', 'jean@example.fr'],
            ['Sophie', 'Leblanc', 'sophie@example.fr'],
            ['Paul', 'Moreau', 'paul@example.fr'],
        ];
        foreach ($loisirData as [$firstName, $lastName, $email]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setRoles(['ROLE_MEMBER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'Member@BoisguiBad2026!'));
            $user->setIsVerified(true);
            $user->setMemberType(MemberType::LOISIR);
            $manager->persist($user);
            $loisirMembers[] = $user;
        }

        // ---- Membres COMPETITEUR ----
        $competMembers = [];
        $competData = [
            ['Lucas', 'Petit', 'lucas@example.fr'],
            ['Emma', 'Durand', 'emma@example.fr'],
            ['Thomas', 'Girard', 'thomas@example.fr'],
            ['Léa', 'Roux', 'lea@example.fr'],
            ['Maxime', 'Fournier', 'maxime@example.fr'],
        ];
        foreach ($competData as [$firstName, $lastName, $email]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setRoles(['ROLE_MEMBER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'Member@BoisguiBad2026!'));
            $user->setIsVerified(true);
            $user->setMemberType(MemberType::COMPETITEUR);
            $manager->persist($user);
            $competMembers[] = $user;
        }

        // ---- Séances passées ----
        $pastSessions = [];
        foreach (['-3 weeks', '-2 weeks', '-1 week'] as $offset) {
            foreach ([SessionType::LUNDI, SessionType::MERCREDI, SessionType::JEUDI] as $type) {
                $session = new Session();
                $session->setType($type);
                $session->setDate(new \DateTimeImmutable($offset . ' monday 20:15'));
                $session->setLocation('Gymnase BoisguiBad');
                $session->setResponsableKeys($admin);
                $manager->persist($session);
                $pastSessions[] = $session;
            }
        }

        // ---- Séances à venir ----
        $upcomingSessions = [];
        foreach (['+0 weeks', '+1 week', '+2 weeks', '+3 weeks'] as $offset) {
            foreach ([SessionType::LUNDI, SessionType::MERCREDI, SessionType::JEUDI] as $type) {
                $session = new Session();
                $session->setType($type);
                $session->setDate(new \DateTimeImmutable($offset . ' monday 20:15'));
                $session->setLocation('Gymnase BoisguiBad');
                // Laisser certaines sessions sans responsable
                if (random_int(0, 2) > 0) {
                    $session->setResponsableKeys($admin);
                }
                $manager->persist($session);
                $upcomingSessions[] = $session;
            }
        }

        // ---- Inscriptions aux séances ----
        $allMembers = array_merge($loisirMembers, $competMembers);
        foreach ($upcomingSessions as $session) {
            $attendees = array_slice($allMembers, 0, random_int(2, 5));
            foreach ($attendees as $member) {
                // Vérification accès (MERCREDI = COMPETITEUR seulement)
                if ($session->getType() === SessionType::MERCREDI && $member->getMemberType() === MemberType::LOISIR) {
                    continue;
                }
                $reg = new SessionRegistration();
                $reg->setSession($session);
                $reg->setUser($member);
                $reg->setPresent(true);
                $manager->persist($reg);
            }
        }

        // ---- Événements ----
        $pastEvent = new Event();
        $pastEvent->setTitle('Tournoi Inter-clubs Printemps 2025');
        $pastEvent->setDescription('Tournoi amical entre clubs de la région.');
        $pastEvent->setDate(new \DateTimeImmutable('-2 months'));
        $pastEvent->setLocation('Complexe Sportif de Rennes');
        $pastEvent->setMaxParticipants(20);
        $manager->persist($pastEvent);

        $upcomingEvent = new Event();
        $upcomingEvent->setTitle('Tournoi d\'Été BoisguiBad 2026');
        $upcomingEvent->setDescription('Tournoi interne de fin de saison. Ouvert à tous les membres.');
        $upcomingEvent->setDate(new \DateTimeImmutable('+2 months'));
        $upcomingEvent->setLocation('Gymnase BoisguiBad');
        $upcomingEvent->setMaxParticipants(32);
        $upcomingEvent->setRegistrationDeadline(new \DateTimeImmutable('+6 weeks'));
        $manager->persist($upcomingEvent);

        // ---- Inscriptions aux événements ----
        foreach (array_slice($allMembers, 0, 6) as $member) {
            $reg = new EventRegistration();
            $reg->setEvent($upcomingEvent);
            $reg->setUser($member);
            $reg->setStatus(EventRegistrationStatus::INSCRIT);
            $manager->persist($reg);
        }

        // Quelques désistements
        $desistReg = new EventRegistration();
        $desistReg->setEvent($upcomingEvent);
        $desistReg->setUser($allMembers[7]);
        $desistReg->setStatus(EventRegistrationStatus::DESISTE);
        $manager->persist($desistReg);

        // ---- Objets trouvés ----
        $lostItemsData = [
            ['Raquette Yonex bleue', LostItemStatus::TROUVE, $loisirMembers[0]],
            ['Chaussures de sport pointure 42', LostItemStatus::TROUVE, $loisirMembers[1]],
            ['Sac de sport noir', LostItemStatus::RENDU, $competMembers[0]],
            ['Bandeau de tête rouge', LostItemStatus::TROUVE, $competMembers[1]],
            ['Bouteille d\'eau bleue', LostItemStatus::RENDU, $loisirMembers[2]],
        ];

        foreach ($lostItemsData as [$description, $status, $declaredBy]) {
            $item = new LostItem();
            $item->setDescription($description);
            $item->setFoundAt(new \DateTimeImmutable('-' . random_int(1, 30) . ' days'));
            $item->setLocation('Gymnase BoisguiBad');
            $item->setStatus($status);
            $item->setDeclaredBy($declaredBy);
            $manager->persist($item);
        }

        $manager->flush();
    }
}
