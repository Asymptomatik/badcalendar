<?php

namespace App\Enum;

enum EventRegistrationStatus: string
{
    case INSCRIT = 'INSCRIT';
    case DESISTE = 'DESISTE';
    case EN_ATTENTE_VALIDATION = 'EN_ATTENTE_VALIDATION';

    public function label(): string
    {
        return match($this) {
            EventRegistrationStatus::INSCRIT => 'Inscrit',
            EventRegistrationStatus::DESISTE => 'Désisté',
            EventRegistrationStatus::EN_ATTENTE_VALIDATION => 'En attente de validation',
        };
    }
}
