<?php

namespace App\Entity;

use App\Repository\CharacterAbilityScoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterAbilityScoreRepository::class)]
#[ORM\UniqueConstraint(name: 'CHARACTER_ABILITY_UNIQ', fields: ['character', 'abilityKey'])]
class CharacterAbilityScore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\Column(length: 10)]
    private ?string $abilityKey = null; // STR, DEX, etc.

    #[ORM\Column]
    private ?int $score = 10;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function setCharacter(?Character $character): static
    {
        $this->character = $character;

        return $this;
    }

    public function getAbilityKey(): ?string
    {
        return $this->abilityKey;
    }

    public function setAbilityKey(string $abilityKey): static
    {
        $this->abilityKey = $abilityKey;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }
}
