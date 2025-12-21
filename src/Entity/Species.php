<?php

namespace App\Entity;

use App\Repository\SpeciesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpeciesRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_KEY', fields: ['rulesSource', 'ruleSlug'])]
class Species
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isActive = true;



    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?RulesSource $rulesSource = null;

    #[ORM\Column(length: 100)]
    private ?string $ruleSlug = null;


    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $size = null;

    #[ORM\Column]
    private ?int $speedWalk = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $asiDescription = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $asi = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $age = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alignment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $speedDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $languages = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $vision = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $traits = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
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

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSpeedWalk(): ?int
    {
        return $this->speedWalk;
    }

    public function setSpeedWalk(int $speedWalk): static
    {
        $this->speedWalk = $speedWalk;

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

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getAlignment(): ?string
    {
        return $this->alignment;
    }

    public function setAlignment(?string $alignment): static
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function getSpeedDescription(): ?string
    {
        return $this->speedDescription;
    }

    public function setSpeedDescription(?string $speedDescription): static
    {
        $this->speedDescription = $speedDescription;

        return $this;
    }

    public function getLanguages(): ?string
    {
        return $this->languages;
    }

    public function setLanguages(?string $languages): static
    {
        $this->languages = $languages;

        return $this;
    }

    public function getVision(): ?string
    {
        return $this->vision;
    }

    public function setVision(?string $vision): static
    {
        $this->vision = $vision;

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
