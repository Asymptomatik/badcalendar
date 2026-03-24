<?php

namespace App\Enum;

enum SessionType: string
{
    case LUNDI = 'LUNDI';
    case MERCREDI = 'MERCREDI';
    case JEUDI = 'JEUDI';
    case DIMANCHE = 'DIMANCHE';

    public function label(): string
    {
        return match($this) {
            SessionType::LUNDI => 'Lundi',
            SessionType::MERCREDI => 'Mercredi',
            SessionType::JEUDI => 'Jeudi',
            SessionType::DIMANCHE => 'Dimanche',
        };
    }

    public function isRecurrent(): bool
    {
        return $this !== SessionType::DIMANCHE;
    }
}
