<?php

namespace App\Entity;

use App\Repository\ClassDefRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassDefRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_KEY', fields: ['rulesSource', 'ruleSlug'])]
class ClassDef
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

    #[ORM\Column]
    private ?int $hitDie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $primaryAbilities = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $savingThrowProficiencies = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hpAt1stLevel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hpAtHigherLevels = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $profArmor = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $profWeapons = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $profTools = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $profSkills = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $equipment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $classTableMd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $characterCreationHelp = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $spellcastingAbility = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $subtypesName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, ClassLevel>
     */
    #[ORM\OneToMany(targetEntity: ClassLevel::class, mappedBy: 'classDef', orphanRemoval: true)]
    private Collection $classLevels;

    /**
     * @var Collection<int, SubclassDef>
     */
    #[ORM\OneToMany(targetEntity: SubclassDef::class, mappedBy: 'classDef', orphanRemoval: true)]
    private Collection $subclasses;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->classLevels = new ArrayCollection();
        $this->subclasses = new ArrayCollection();
    }

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

    public function getHitDie(): ?int
    {
        return $this->hitDie;
    }

    public function setHitDie(int $hitDie): static
    {
        $this->hitDie = $hitDie;

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

    public function getPrimaryAbilities(): ?array
    {
        return $this->primaryAbilities;
    }

    public function setPrimaryAbilities(?array $primaryAbilities): static
    {
        $this->primaryAbilities = $primaryAbilities;

        return $this;
    }

    public function getSavingThrowProficiencies(): ?array
    {
        return $this->savingThrowProficiencies;
    }

    public function setSavingThrowProficiencies(?array $savingThrowProficiencies): static
    {
        $this->savingThrowProficiencies = $savingThrowProficiencies;

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

    /**
     * @return Collection<int, ClassLevel>
     */
    public function getClassLevels(): Collection
    {
        return $this->classLevels;
    }

    public function addClassLevel(ClassLevel $classLevel): static
    {
        if (!$this->classLevels->contains($classLevel)) {
            $this->classLevels->add($classLevel);
            $classLevel->setClassDef($this);
        }

        return $this;
    }

    public function removeClassLevel(ClassLevel $classLevel): static
    {
        if ($this->classLevels->removeElement($classLevel)) {
            // set the owning side to null (unless already changed)
            if ($classLevel->getClassDef() === $this) {
                $classLevel->setClassDef(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubclassDef>
     */
    public function getSubclasses(): Collection
    {
        return $this->subclasses;
    }

    public function addSubclass(SubclassDef $subclass): static
    {
        if (!$this->subclasses->contains($subclass)) {
            $this->subclasses->add($subclass);
            $subclass->setClassDef($this);
        }

        return $this;
    }

    public function removeSubclass(SubclassDef $subclass): static
    {
        if ($this->subclasses->removeElement($subclass)) {
            // set the owning side to null (unless already changed)
            if ($subclass->getClassDef() === $this) {
                $subclass->setClassDef(null);
            }
        }

        return $this;
    }

    public function getHpAt1stLevel(): ?string
    {
        return $this->hpAt1stLevel;
    }

    public function setHpAt1stLevel(?string $hpAt1stLevel): static
    {
        $this->hpAt1stLevel = $hpAt1stLevel;

        return $this;
    }

    public function getHpAtHigherLevels(): ?string
    {
        return $this->hpAtHigherLevels;
    }

    public function setHpAtHigherLevels(?string $hpAtHigherLevels): static
    {
        $this->hpAtHigherLevels = $hpAtHigherLevels;

        return $this;
    }

    public function getProfArmor(): ?string
    {
        return $this->profArmor;
    }

    public function setProfArmor(?string $profArmor): static
    {
        $this->profArmor = $profArmor;

        return $this;
    }

    public function getProfWeapons(): ?string
    {
        return $this->profWeapons;
    }

    public function setProfWeapons(?string $profWeapons): static
    {
        $this->profWeapons = $profWeapons;

        return $this;
    }

    public function getProfTools(): ?string
    {
        return $this->profTools;
    }

    public function setProfTools(?string $profTools): static
    {
        $this->profTools = $profTools;

        return $this;
    }

    public function getProfSkills(): ?string
    {
        return $this->profSkills;
    }

    public function setProfSkills(?string $profSkills): static
    {
        $this->profSkills = $profSkills;

        return $this;
    }

    public function getEquipment(): ?string
    {
        return $this->equipment;
    }

    public function setEquipment(?string $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getClassTableMd(): ?string
    {
        return $this->classTableMd;
    }

    public function setClassTableMd(?string $classTableMd): static
    {
        $this->classTableMd = $classTableMd;

        return $this;
    }

    public function getSpellcastingAbility(): ?string
    {
        return $this->spellcastingAbility;
    }

    public function setSpellcastingAbility(?string $spellcastingAbility): static
    {
        $this->spellcastingAbility = $spellcastingAbility;

        return $this;
    }

    public function getSubtypesName(): ?string
    {
        return $this->subtypesName;
    }

    public function setSubtypesName(?string $subtypesName): static
    {
        $this->subtypesName = $subtypesName;

        return $this;
    }

    public function getCharacterCreationHelp(): ?string
    {
        return $this->characterCreationHelp;
    }

    public function setCharacterCreationHelp(?string $characterCreationHelp): static
    {
        $this->characterCreationHelp = $characterCreationHelp;

        return $this;
    }
}
