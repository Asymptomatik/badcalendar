<?php

namespace App\Enum;

enum MemberType: string
{
    case LOISIR = 'LOISIR';
    case COMPETITEUR = 'COMPETITEUR';

    public function label(): string
    {
        return match($this) {
            MemberType::LOISIR => 'Loisir',
            MemberType::COMPETITEUR => 'Compétiteur',
        };
    }
}
