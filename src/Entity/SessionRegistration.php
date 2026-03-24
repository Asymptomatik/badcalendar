<?php

namespace App\Entity;

use App\Repository\SessionRegistrationRepository;
use App\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRegistrationRepository::class)]
#[ORM\Table(name: 'session_registration')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'unique_session_user', columns: ['session_id', 'user_id'])]
class SessionRegistration
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessionRegistrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /** Présence confirmée (pour séances du dimanche) */
    #[ORM\Column(type: 'boolean')]
    private bool $present = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): static
    {
        $this->present = $present;
        return $this;
    }
}
