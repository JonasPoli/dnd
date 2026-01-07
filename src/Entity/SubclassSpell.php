<?php

namespace App\Entity;

use App\Repository\SubclassSpellRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubclassSpellRepository::class)]
class SubclassSpell
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubclassDef $subclassDef = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Spell $spell = null;

    #[ORM\Column]
    private ?int $levelAcquired = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubclassDef(): ?SubclassDef
    {
        return $this->subclassDef;
    }

    public function setSubclassDef(?SubclassDef $subclassDef): static
    {
        $this->subclassDef = $subclassDef;

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

    public function getLevelAcquired(): ?int
    {
        return $this->levelAcquired;
    }

    public function setLevelAcquired(int $levelAcquired): static
    {
        $this->levelAcquired = $levelAcquired;

        return $this;
    }
}
