<?php

namespace App\Enum;

enum SpellSchool: string
{
    case ABJURATION = 'Abjuração';
    case DIVINATION = 'Adivinhação';
    case CONJURATION = 'Conjuração';
    case ENCHANTMENT = 'Encantamento';
    case EVOCATION = 'Evocação';
    case ILLUSION = 'Ilusão';
    case NECROMANCY = 'Necromancia';
    case TRANSMUTATION = 'Transmutação';

    public function getImagePath(): string
    {
        return '/media/spell/' . $this->value . '.png';
    }
}
