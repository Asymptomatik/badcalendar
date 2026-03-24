<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;

interface EventServiceInterface
{
    /** Inscrit un membre à un événement */
    public function register(Event $event, User $user): void;

    /** Désiste un membre d'un événement */
    public function desist(Event $event, User $user): void;

    /** Confirme la participation d'un membre à un événement */
    public function confirm(Event $event, User $user): void;
}
