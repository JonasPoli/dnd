<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $level = 1;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClassDef $classDef = null;

    #[ORM\ManyToOne]
    private ?SubclassDef $subclassDef = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Species $species = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Background $background = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $alignment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: CharacterSpell::class, mappedBy: 'character', orphanRemoval: true)]
    private Collection $characterSpells;

    #[ORM\ManyToMany(targetEntity: Skill::class)]
    #[ORM\JoinTable(name: 'character_skill')]
    private Collection $skills;

    #[ORM\ManyToMany(targetEntity: Equipment::class)]
    #[ORM\JoinTable(name: 'character_tool_proficiency')]
    private Collection $toolProficiencies;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->characterSpells = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->toolProficiencies = new ArrayCollection();
    }

    // ... existing getters ...

    /**
     * @return Collection<int, CharacterSpell>
     */
    public function getCharacterSpells(): Collection
    {
        return $this->characterSpells;
    }

    public function addCharacterSpell(CharacterSpell $characterSpell): static
    {
        if (!$this->characterSpells->contains($characterSpell)) {
            $this->characterSpells->add($characterSpell);
            $characterSpell->setCharacter($this);
        }

        return $this;
    }

    public function removeCharacterSpell(CharacterSpell $characterSpell): static
    {
        if ($this->characterSpells->removeElement($characterSpell)) {
            // set the owning side to null (unless already changed)
            if ($characterSpell->getCharacter() === $this) {
                $characterSpell->setCharacter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getToolProficiencies(): Collection
    {
        return $this->toolProficiencies;
    }

    public function addToolProficiency(Equipment $toolProficiency): static
    {
        if (!$this->toolProficiencies->contains($toolProficiency)) {
            $this->toolProficiencies->add($toolProficiency);
        }

        return $this;
    }

    public function removeToolProficiency(Equipment $toolProficiency): static
    {
        $this->toolProficiencies->removeElement($toolProficiency);

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
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

    public function getSubclassDef(): ?SubclassDef
    {
        return $this->subclassDef;
    }

    public function setSubclassDef(?SubclassDef $subclassDef): static
    {
        $this->subclassDef = $subclassDef;

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

    public function getBackground(): ?Background
    {
        return $this->background;
    }

    public function setBackground(?Background $background): static
    {
        $this->background = $background;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
