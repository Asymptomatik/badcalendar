<?php

namespace App\Entity;

use App\Enum\SessionType;
use App\Repository\SessionRepository;
use App\Trait\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: 'session')]
#[ORM\HasLifecycleCallbacks]
class Session
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: SessionType::class)]
    private ?SessionType $type = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessionResponsabilities')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $responsableKeys = null;

    /** @var Collection<int, SessionRegistration> */
    #[ORM\OneToMany(targetEntity: SessionRegistration::class, mappedBy: 'session', cascade: ['persist', 'remove'])]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?SessionType
    {
        return $this->type;
    }

    public function setType(SessionType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getResponsableKeys(): ?User
    {
        return $this->responsableKeys;
    }

    public function setResponsableKeys(?User $responsableKeys): static
    {
        $this->responsableKeys = $responsableKeys;
        return $this;
    }

    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(SessionRegistration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setSession($this);
        }
        return $this;
    }

    public function removeRegistration(SessionRegistration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            if ($registration->getSession() === $this) {
                $registration->setSession(null);
            }
        }
        return $this;
    }

    public function isRegistered(User $user): bool
    {
        foreach ($this->registrations as $registration) {
            if ($registration->getUser() === $user) {
                return true;
            }
        }
        return false;
    }
}
