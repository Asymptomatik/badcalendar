<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\User;

interface SessionServiceInterface
{
    /** Inscrit un membre à une séance */
    public function register(Session $session, User $user): void;

    /** Désinscrit un membre d'une séance */
    public function unregister(Session $session, User $user): void;

    /** Vérifie si un membre peut accéder à une séance selon son type */
    public function canAccess(Session $session, User $user): bool;

    /** Assigne le responsable clés d'une séance */
    public function assignResponsableKeys(Session $session, User $user): void;
}
