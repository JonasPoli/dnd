<?php

namespace App\Enum;

enum ArmorTraining: string
{
    case NONE = 'none';
    case LIGHT_MEDIUM_SHIELDS = 'light_medium_shields';
    case LIGHT = 'light';
    case LIGHT_SHIELDS = 'light_shields';
    case ALL_SHIELDS = 'all_shields';
    case SIMPLE_MARTIAL_FINESSE_LIGHT = 'simple_martial_finesse_light';
    case SIMPLE_MARTIAL_LIGHT = 'simple_martial_light';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'Nenhum',
            self::LIGHT_MEDIUM_SHIELDS => 'Armaduras Leve, Média e Escudos',
            self::LIGHT => 'Armadura Leve',
            self::LIGHT_SHIELDS => 'Armadura Leve e Escudos',
            self::ALL_SHIELDS => 'Armaduras Leves, Médias e Pesadas e Escudos',
            self::SIMPLE_MARTIAL_FINESSE_LIGHT => 'Armas Simples e Armas Marciais que tem a propriedade Acuidade ou Leve',
            self::SIMPLE_MARTIAL_LIGHT => 'Armas Simples e Marciais que têm a propriedade Leve',
        };
    }
}
