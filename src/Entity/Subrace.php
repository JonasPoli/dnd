<?php

namespace App\Entity;

use App\Repository\SubraceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubraceRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SPECIES_KEY', fields: ['species', 'ruleSlug'])]
class Subrace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?RulesSource $rulesSource = null;

    #[ORM\ManyToOne(inversedBy: 'subraces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Species $species = null;

    #[ORM\Column(length: 100)]
    private ?string $ruleSlug = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $asiDescription = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $asi = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $traits = null;

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

    public function getSpecies(): ?Species
    {
        return $this->species;
    }

    public function setSpecies(?Species $species): static
    {
        $this->species = $species;

        return $this;
    }

    public function getRuleSlug(): ?string
    {
        return $this->ruleSlug;
    }

    public function setRuleSlug(string $ruleSlug): static
    {
        $this->ruleSlug = $ruleSlug;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescriptionMd(): ?string
    {
        return $this->descriptionMd;
    }

    public function setDescriptionMd(?string $descriptionMd): static
    {
        $this->descriptionMd = $descriptionMd;

        return $this;
    }

    public function getAsiDescription(): ?string
    {
        return $this->asiDescription;
    }

    public function setAsiDescription(?string $asiDescription): static
    {
        $this->asiDescription = $asiDescription;

        return $this;
    }

    public function getAsi(): array
    {
        return $this->asi;
    }

    public function setAsi(?array $asi): static
    {
        $this->asi = $asi;

        return $this;
    }

    public function getTraits(): ?string
    {
        return $this->traits;
    }

    public function setTraits(?string $traits): static
    {
        $this->traits = $traits;

        return $this;
    }
}
