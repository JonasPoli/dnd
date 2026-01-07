<?php

namespace App\Enum;

enum WeaponProficiency: string
{
    case NONE = 'none';
    case SIMPLE_MARTIAL = 'simple_martial';
    case SIMPLE = 'simple';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Nenhum',
            self::SIMPLE_MARTIAL => 'Armas Simples e Marciais',
            self::SIMPLE => 'Armas Simples',
        };
    }
}
