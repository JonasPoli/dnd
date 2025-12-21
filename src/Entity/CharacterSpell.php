<?php

namespace App\Entity;

use App\Repository\CharacterSpellRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterSpellRepository::class)]
class CharacterSpell
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Spell $spell = null;

    #[ORM\Column]
    private ?int $learnedAtLevel = 1;

    #[ORM\Column]
    private ?bool $prepared = false;

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

    public function getSpell(): ?Spell
    {
        return $this->spell;
    }

    public function setSpell(?Spell $spell): static
    {
        $this->spell = $spell;

        return $this;
    }

    public function getLearnedAtLevel(): ?int
    {
        return $this->learnedAtLevel;
    }

    public function setLearnedAtLevel(int $learnedAtLevel): static
    {
        $this->learnedAtLevel = $learnedAtLevel;

        return $this;
    }

    public function isPrepared(): ?bool
    {
        return $this->prepared;
    }

    public function setPrepared(bool $prepared): static
    {
        $this->prepared = $prepared;

        return $this;
    }
}
