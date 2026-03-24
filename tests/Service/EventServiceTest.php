<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\User;
use App\Enum\EventRegistrationStatus;
use App\Repository\EventRegistrationRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour EventService.
 */
#[AllowMockObjectsWithoutExpectations]
class EventServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private EventRegistrationRepository&MockObject $registrationRepo;
    private EventService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->registrationRepo = $this->createMock(EventRegistrationRepository::class);
        $this->service = new EventService($this->em, $this->registrationRepo);
    }

    // ---- register ----

    public function testRegisterCreatesNewRegistration(): void
    {
        $event = new Event();
        $user = $this->createUser();

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn(null);
        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(EventRegistration::class));
        $this->em->expects($this->once())->method('flush');

        $this->service->register($event, $user);
    }

    public function testRegisterDoesNothingWhenAlreadyInscrit(): void
    {
        $event = new Event();
        $user = $this->createUser();
        $existing = $this->createRegistration($event, $user, EventRegistrationStatus::INSCRIT);

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn($existing);
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->service->register($event, $user);
    }

    public function testRegisterReactivatesDesiste(): void
    {
        $event = new Event();
        $user = $this->createUser();
        $existing = $this->createRegistration($event, $user, EventRegistrationStatus::DESISTE);

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn($existing);
        $this->em->expects($this->once())->method('flush');

        $this->service->register($event, $user);

        $this->assertEquals(EventRegistrationStatus::INSCRIT, $existing->getStatus());
    }

    // ---- desist ----

    public function testDesistDoesNothingWhenNotRegistered(): void
    {
        $event = new Event();
        $user = $this->createUser();

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        $this->service->desist($event, $user);
    }

    public function testDesistChangesStatus(): void
    {
        $event = new Event();
        $user = $this->createUser();
        $registration = $this->createRegistration($event, $user, EventRegistrationStatus::INSCRIT);

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn($registration);
        $this->em->expects($this->once())->method('flush');

        $this->service->desist($event, $user);

        $this->assertEquals(EventRegistrationStatus::DESISTE, $registration->getStatus());
    }

    // ---- confirm ----

    public function testConfirmDoesNothingWhenNotRegistered(): void
    {
        $event = new Event();
        $user = $this->createUser();

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn(null);
        $this->em->expects($this->never())->method('flush');

        $this->service->confirm($event, $user);
    }

    public function testConfirmSetsConfirmedAt(): void
    {
        $event = new Event();
        $user = $this->createUser();
        $registration = $this->createRegistration($event, $user, EventRegistrationStatus::INSCRIT);

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn($registration);
        $this->em->expects($this->once())->method('flush');

        $this->service->confirm($event, $user);

        $this->assertNotNull($registration->getConfirmedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $registration->getConfirmedAt());
    }

    public function testConfirmDoesNothingWhenDesiste(): void
    {
        $event = new Event();
        $user = $this->createUser();
        $registration = $this->createRegistration($event, $user, EventRegistrationStatus::DESISTE);

        $this->registrationRepo->method('findOneByEventAndUser')->willReturn($registration);
        $this->em->expects($this->never())->method('flush');

        $this->service->confirm($event, $user);

        $this->assertNull($registration->getConfirmedAt());
    }

    // ---- Helpers ----

    private function createUser(): User
    {
        $user = new User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@example.fr');
        return $user;
    }

    private function createRegistration(Event $event, User $user, EventRegistrationStatus $status): EventRegistration
    {
        $reg = new EventRegistration();
        $reg->setEvent($event);
        $reg->setUser($user);
        $reg->setStatus($status);
        return $reg;
    }
}
