<?php

namespace App\Entity;

use App\Repository\ClassLevelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassLevelRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_CLASS_LEVEL', fields: ['classDef', 'level'])]
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
    private ?int $level = null;

    #[ORM\Column]
    private ?int $proficiencyBonus = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $spellSlotsJson = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesMd = null;

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

    public function getNotesMd(): ?string
    {
        return $this->notesMd;
    }

    public function setNotesMd(?string $notesMd): static
    {
        $this->notesMd = $notesMd;

        return $this;
    }
}
