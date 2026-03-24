<?php

namespace App\Tests\Service;

use App\Entity\Session;
use App\Enum\SessionType;
use App\Repository\SessionRepository;
use App\Service\SessionSchedulerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour SessionSchedulerService.
 */
#[AllowMockObjectsWithoutExpectations]
class SessionSchedulerServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private SessionRepository&MockObject $sessionRepo;
    private SessionSchedulerService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->sessionRepo = $this->createMock(SessionRepository::class);
        $this->service = new SessionSchedulerService($this->em, $this->sessionRepo);
    }

    public function testGeneratesSessionsWhenNoneExist(): void
    {
        // Aucune session existante
        $this->sessionRepo->method('findOneByTypeAndDate')->willReturn(null);

        // 1 semaine → 3 types (lundi, mercredi, jeudi)
        $this->em->expects($this->exactly(3))->method('persist')->with($this->isInstanceOf(Session::class));
        $this->em->expects($this->once())->method('flush');

        $created = $this->service->generateUpcomingSessions(1);
        $this->assertEquals(3, $created);
    }

    public function testIdempotentWhenSessionsAlreadyExist(): void
    {
        $existingSession = new Session();
        // Toutes les sessions existent déjà
        $this->sessionRepo->method('findOneByTypeAndDate')->willReturn($existingSession);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $created = $this->service->generateUpcomingSessions(1);
        $this->assertEquals(0, $created);
    }

    public function testGeneratesCorrectNumberOfSessionsForMultipleWeeks(): void
    {
        $this->sessionRepo->method('findOneByTypeAndDate')->willReturn(null);

        // 3 semaines × 3 types = 9 sessions
        $this->em->expects($this->exactly(9))->method('persist');
        $this->em->expects($this->once())->method('flush');

        $created = $this->service->generateUpcomingSessions(3);
        $this->assertEquals(9, $created);
    }

    public function testGeneratedSessionsHaveCorrectTypes(): void
    {
        $generatedTypes = [];

        $this->sessionRepo->method('findOneByTypeAndDate')->willReturn(null);
        $this->em->method('persist')->willReturnCallback(function (Session $session) use (&$generatedTypes) {
            $generatedTypes[] = $session->getType();
        });
        $this->em->method('flush');

        $this->service->generateUpcomingSessions(1);

        $this->assertContains(SessionType::LUNDI, $generatedTypes);
        $this->assertContains(SessionType::MERCREDI, $generatedTypes);
        $this->assertContains(SessionType::JEUDI, $generatedTypes);
        $this->assertNotContains(SessionType::DIMANCHE, $generatedTypes);
    }
}
