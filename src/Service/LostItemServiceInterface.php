<?php

namespace App\Service;

use App\Entity\LostItem;
use App\Entity\User;

interface LostItemServiceInterface
{
    /** Déclare un objet trouvé */
    public function declare(LostItem $lostItem, User $declaredBy): void;

    /** Marque un objet comme rendu */
    public function markAsReturned(LostItem $lostItem): void;
}
