<?php

namespace App\Entity;

use App\Enum\LostItemStatus;
use App\Repository\LostItemRepository;
use App\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LostItemRepository::class)]
#[ORM\Table(name: 'lost_item')]
#[ORM\HasLifecycleCallbacks]
class LostItem
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $foundAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoFilename = null;

    #[ORM\Column(enumType: LostItemStatus::class)]
    private ?LostItemStatus $status = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $declaredBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getFoundAt(): ?\DateTimeImmutable
    {
        return $this->foundAt;
    }

    public function setFoundAt(\DateTimeImmutable $foundAt): static
    {
        $this->foundAt = $foundAt;
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

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): static
    {
        $this->photoFilename = $photoFilename;
        return $this;
    }

    public function getStatus(): ?LostItemStatus
    {
        return $this->status;
    }

    public function setStatus(LostItemStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDeclaredBy(): ?User
    {
        return $this->declaredBy;
    }

    public function setDeclaredBy(?User $declaredBy): static
    {
        $this->declaredBy = $declaredBy;
        return $this;
    }
}
