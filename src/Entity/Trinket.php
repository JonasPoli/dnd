<?php

namespace App\Entity;

use App\Repository\TrinketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrinketRepository::class)]
class Trinket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?RulesSource $rulesSource = null;

    #[ORM\Column]
    private ?int $rollKey = null; // d100 result

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textMd = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRulesSource(): ?RulesSource
    {
        return $this->rulesSource;
    }

    public function setRulesSource(?RulesSource $rulesSource): static
    {
        $this->rulesSource = $rulesSource;

        return $this;
    }

    public function getRollKey(): ?int
    {
        return $this->rollKey;
    }

    public function setRollKey(int $rollKey): static
    {
        $this->rollKey = $rollKey;

        return $this;
    }

    public function getTextMd(): ?string
    {
        return $this->textMd;
    }

    public function setTextMd(string $textMd): static
    {
        $this->textMd = $textMd;

        return $this;
    }
}
