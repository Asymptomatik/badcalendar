<?php

namespace App\Service;

use App\Entity\LostItem;
use App\Entity\User;
use App\Enum\LostItemStatus;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service métier pour la gestion des objets trouvés.
 */
class LostItemService implements LostItemServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /** Déclare un objet trouvé */
    public function declare(LostItem $lostItem, User $declaredBy): void
    {
        $lostItem->setDeclaredBy($declaredBy);
        $lostItem->setStatus(LostItemStatus::TROUVE);

        $this->em->persist($lostItem);
        $this->em->flush();
    }

    /** Marque un objet comme rendu */
    public function markAsReturned(LostItem $lostItem): void
    {
        $lostItem->setStatus(LostItemStatus::RENDU);
        $this->em->flush();
    }
}
