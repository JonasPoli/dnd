<?php

namespace App\Enum;

enum WeaponProficiency: string
{
    case NONE = 'none';
    case SIMPLE_MARTIAL = 'simple_martial';
    case SIMPLE = 'simple';

    case SIMPLE_MARTIAL_FINESSE_LIGHT = 'simple_martial_finesse_light';
    case SIMPLE_MARTIAL_LIGHT = 'simple_martial_light';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Nenhum',
            self::SIMPLE_MARTIAL => 'Armas Simples e Marciais',
            self::SIMPLE => 'Armas Simples',
            self::SIMPLE_MARTIAL_FINESSE_LIGHT => 'Armas Simples e Marciais (Acuidade/Leve)',
            self::SIMPLE_MARTIAL_LIGHT => 'Armas Simples e Marciais (Leve)',
        };
    }
}
