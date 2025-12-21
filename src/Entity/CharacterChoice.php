<?php

namespace App\Entity;

use App\Repository\CharacterChoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterChoiceRepository::class)]
class CharacterChoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\Column(length: 100)]
    private ?string $stepKey = null;

    #[ORM\Column(length: 100)]
    private ?string $choiceKey = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $valueJson = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getStepKey(): ?string
    {
        return $this->stepKey;
    }

    public function setStepKey(string $stepKey): static
    {
        $this->stepKey = $stepKey;

        return $this;
    }

    public function getChoiceKey(): ?string
    {
        return $this->choiceKey;
    }

    public function setChoiceKey(string $choiceKey): static
    {
        $this->choiceKey = $choiceKey;

        return $this;
    }

    public function getValueJson(): ?array
    {
        return $this->valueJson;
    }

    public function setValueJson(array $valueJson): static
    {
        $this->valueJson = $valueJson;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
