<?php

namespace App\Enum;

enum LostItemStatus: string
{
    case TROUVE = 'TROUVE';
    case RENDU = 'RENDU';

    public function label(): string
    {
        return match($this) {
            LostItemStatus::TROUVE => 'Trouvé',
            LostItemStatus::RENDU => 'Rendu',
        };
    }
}
