<?php

namespace App\Tests\Service;

use App\Entity\Session;
use App\Entity\SessionRegistration;
use App\Entity\User;
use App\Enum\MemberType;
use App\Enum\SessionType;
use App\Repository\SessionRegistrationRepository;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Tests unitaires pour SessionService.
 */
#[AllowMockObjectsWithoutExpectations]
class SessionServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private SessionRegistrationRepository&MockObject $registrationRepo;
    private SessionService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registrationRepo = $this->createMock(SessionRegistrationRepository::class);
        $this->service = new SessionService($this->em, $this->registrationRepo);
    }

    // ---- canAccess ----

    public function testCanAccessReturnsFalseWhenMemberTypeIsNull(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(null);

        $this->assertFalse($this->service->canAccess($session, $user));
    }

    public function testLoisirCanAccessLundi(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(MemberType::LOISIR);

        $this->assertTrue($this->service->canAccess($session, $user));
    }

    public function testLoisirCanAccessJeudi(): void
    {
        $session = $this->createSession(SessionType::JEUDI);
        $user = $this->createUser(MemberType::LOISIR);

        $this->assertTrue($this->service->canAccess($session, $user));
    }

    public function testLoisirCanAccessDimanche(): void
    {
        $session = $this->createSession(SessionType::DIMANCHE);
        $user = $this->createUser(MemberType::LOISIR);

        $this->assertTrue($this->service->canAccess($session, $user));
    }

    public function testLoisirCannotAccessMercredi(): void
    {
        $session = $this->createSession(SessionType::MERCREDI);
        $user = $this->createUser(MemberType::LOISIR);

        $this->assertFalse($this->service->canAccess($session, $user));
    }

    public function testCompetiteurCanAccessMercredi(): void
    {
        $session = $this->createSession(SessionType::MERCREDI);
        $user = $this->createUser(MemberType::COMPETITEUR);

        $this->assertTrue($this->service->canAccess($session, $user));
    }

    public function testCompetiteurCanAccessAllSessions(): void
    {
        $user = $this->createUser(MemberType::COMPETITEUR);

        foreach (SessionType::cases() as $type) {
            $session = $this->createSession($type);
            $this->assertTrue($this->service->canAccess($session, $user), "Compétiteur should access " . $type->value);
        }
    }

    public function testFilterAccessibleSessionsRemovesMercrediForLoisir(): void
    {
        $user = $this->createUser(MemberType::LOISIR);
        $sessions = [
            $this->createSession(SessionType::LUNDI),
            $this->createSession(SessionType::MERCREDI),
            $this->createSession(SessionType::JEUDI),
            $this->createSession(SessionType::DIMANCHE),
        ];

        $filteredSessions = $this->service->filterAccessibleSessions($sessions, $user);

        $this->assertCount(3, $filteredSessions);
        $this->assertSame(
            [SessionType::LUNDI, SessionType::JEUDI, SessionType::DIMANCHE],
            array_map(static fn (Session $session): SessionType => $session->getType(), $filteredSessions)
        );
    }

    public function testFilterAccessibleSessionsKeepsAllSessionsForCompetiteur(): void
    {
        $user = $this->createUser(MemberType::COMPETITEUR);
        $sessions = array_map(fn (SessionType $type): Session => $this->createSession($type), SessionType::cases());

        $filteredSessions = $this->service->filterAccessibleSessions($sessions, $user);

        $this->assertCount(count(SessionType::cases()), $filteredSessions);
    }

    // ---- register ----

    public function testRegisterThrowsExceptionWhenUserCannotAccess(): void
    {
        $session = $this->createSession(SessionType::MERCREDI);
        $user = $this->createUser(MemberType::LOISIR);

        $this->registrationRepo->expects($this->never())->method('findOneBySessionAndUser');

        $this->expectException(AccessDeniedException::class);
        $this->service->register($session, $user);
    }

    public function testRegisterDoesNothingWhenAlreadyRegistered(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(MemberType::LOISIR);
        $existingRegistration = new SessionRegistration();

        $this->registrationRepo->method('findOneBySessionAndUser')->willReturn($existingRegistration);
        $this->em->expects($this->never())->method('persist');

        $this->service->register($session, $user);
    }

    public function testRegisterCreatesNewRegistration(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(MemberType::LOISIR);

        $this->registrationRepo->method('findOneBySessionAndUser')->willReturn(null);
        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(SessionRegistration::class));
        $this->em->expects($this->once())->method('flush');

        $this->service->register($session, $user);
    }

    // ---- unregister ----

    public function testUnregisterDoesNothingWhenNotRegistered(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(MemberType::LOISIR);

        $this->registrationRepo->method('findOneBySessionAndUser')->willReturn(null);
        $this->em->expects($this->never())->method('remove');

        $this->service->unregister($session, $user);
    }

    public function testUnregisterRemovesRegistration(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(MemberType::LOISIR);
        $registration = new SessionRegistration();

        $this->registrationRepo->method('findOneBySessionAndUser')->willReturn($registration);
        $this->em->expects($this->once())->method('remove')->with($registration);
        $this->em->expects($this->once())->method('flush');

        $this->service->unregister($session, $user);
    }

    // ---- assignResponsableKeys ----

    public function testAssignResponsableKeys(): void
    {
        $session = $this->createSession(SessionType::LUNDI);
        $user = $this->createUser(MemberType::COMPETITEUR);

        $this->em->expects($this->once())->method('persist')->with($session);
        $this->em->expects($this->once())->method('flush');

        $this->service->assignResponsableKeys($session, $user);

        $this->assertSame($user, $session->getResponsableKeys());
    }

    // ---- Helpers ----

    private function createSession(SessionType $type): Session
    {
        $session = new Session();
        $session->setType($type);
        $session->setDate(new \DateTimeImmutable('+1 day 20:15'));
        return $session;
    }

    private function createUser(?MemberType $memberType): User
    {
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@example.fr');
        $user->setMemberType($memberType);
        return $user;
    }
}
