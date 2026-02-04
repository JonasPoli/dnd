<?php

namespace App\Entity;

use App\Repository\ClassLevelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClassLevelRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_CLASS_LEVEL', fields: ['classDef', 'level'])]
#[UniqueEntity(
    fields: ['classDef', 'level'],
    errorPath: 'level',
    message: 'Já existe uma configuração para este nível nesta classe.'
)]
class ClassLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'classLevels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClassDef $classDef = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $level = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Range(min: 2, max: 6)]
    private ?int $proficiencyBonus = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $spellSlotsJson = [];

    #[ORM\Column(nullable: true)]
    private ?int $cantripsKnown = null;

    #[ORM\Column(nullable: true)]
    private ?int $spellsPrepared = null;

    #[ORM\Column(nullable: true)]
    private ?int $featsKnown = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $featuresConfig = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesMd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $featuresList = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customDie = null;

    #[ORM\Column(nullable: true)]
    private ?int $customCount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClassDef(): ?ClassDef
    {
        return $this->classDef;
    }

    public function setClassDef(?ClassDef $classDef): static
    {
        $this->classDef = $classDef;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getProficiencyBonus(): ?int
    {
        return $this->proficiencyBonus;
    }

    public function setProficiencyBonus(int $proficiencyBonus): static
    {
        $this->proficiencyBonus = $proficiencyBonus;

        return $this;
    }

    public function getSpellSlotsJson(): ?array
    {
        return $this->spellSlotsJson;
    }

    public function setSpellSlotsJson(?array $spellSlotsJson): static
    {
        $this->spellSlotsJson = $spellSlotsJson;

        return $this;
    }

    public function getCantripsKnown(): ?int
    {
        return $this->cantripsKnown;
    }

    public function setCantripsKnown(?int $cantripsKnown): static
    {
        $this->cantripsKnown = $cantripsKnown;

        return $this;
    }

    public function getSpellsPrepared(): ?int
    {
        return $this->spellsPrepared;
    }

    public function setSpellsPrepared(?int $spellsPrepared): static
    {
        $this->spellsPrepared = $spellsPrepared;

        return $this;
    }

    public function getFeatsKnown(): ?int
    {
        return $this->featsKnown;
    }

    public function setFeatsKnown(?int $featsKnown): static
    {
        $this->featsKnown = $featsKnown;

        return $this;
    }

    public function getFeaturesConfig(): ?array
    {
        return $this->featuresConfig;
    }

    public function setFeaturesConfig(?array $featuresConfig): static
    {
        $this->featuresConfig = $featuresConfig;

        return $this;
    }

    public function getNotesMd(): ?string
    {
        return $this->notesMd;
    }

    public function setNotesMd(?string $notesMd): static
    {
        $this->notesMd = $notesMd;

        return $this;
    }

    public function getFeaturesList(): ?string
    {
        return $this->featuresList;
    }

    public function setFeaturesList(?string $featuresList): static
    {
        $this->featuresList = $featuresList;

        return $this;
    }

    public function getCustomDie(): ?string
    {
        return $this->customDie;
    }

    public function setCustomDie(?string $customDie): static
    {
        $this->customDie = $customDie;

        return $this;
    }

    public function getCustomCount(): ?int
    {
        return $this->customCount;
    }

    public function setCustomCount(?int $customCount): static
    {
        $this->customCount = $customCount;

        return $this;
    }
}
