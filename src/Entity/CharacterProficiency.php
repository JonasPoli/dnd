<?php

namespace App\Entity;

use App\Repository\CharacterProficiencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterProficiencyRepository::class)]
class CharacterProficiency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null; // skill, save, weapon, armor, tool, language

    #[ORM\Column(length: 100)]
    private ?string $refKey = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $sourceText = null; // class, background, etc.

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRefKey(): ?string
    {
        return $this->refKey;
    }

    public function setRefKey(string $refKey): static
    {
        $this->refKey = $refKey;

        return $this;
    }

    public function getSourceText(): ?string
    {
        return $this->sourceText;
    }

    public function setSourceText(?string $sourceText): static
    {
        $this->sourceText = $sourceText;

        return $this;
    }
}
