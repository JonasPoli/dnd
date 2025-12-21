<?php

namespace App\Entity;

use App\Repository\SpellRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpellRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_KEY', fields: ['rulesSource', 'ruleSlug'])]
class Spell
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
    private ?int $level = null;

    #[ORM\Column(length: 100)]
    private ?string $school = null;

    #[ORM\Column(length: 255)]
    private ?string $castingTime = null;

    #[ORM\Column(length: 100)]
    private ?string $spellRange = null;


    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $componentsJson = [];

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $descriptionMd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $higherLevelsMd = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $page = null;

    #[ORM\Column(nullable: true)]
    private ?int $targetRangeSort = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $components = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $material = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isRitual = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isConcentration = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isVerbal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isSomatic = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isMaterial = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $archetype = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $circles = null;

    /**
     * @var Collection<int, ClassDef>
     */
    #[ORM\ManyToMany(targetEntity: ClassDef::class)]
    private Collection $classes;

    public function __construct()
    {
        $this->classes = new ArrayCollection();
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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(string $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getCastingTime(): ?string
    {
        return $this->castingTime;
    }

    public function setCastingTime(string $castingTime): static
    {
        $this->castingTime = $castingTime;

        return $this;
    }

    public function getSpellRange(): ?string
    {
        return $this->spellRange;
    }

    public function setSpellRange(string $spellRange): static
    {
        $this->spellRange = $spellRange;

        return $this;
    }


    public function getComponentsJson(): ?array
    {
        return $this->componentsJson;
    }

    public function setComponentsJson(?array $componentsJson): static
    {
        $this->componentsJson = $componentsJson;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDescriptionMd(): ?string
    {
        return $this->descriptionMd;
    }

    public function setDescriptionMd(string $descriptionMd): static
    {
        $this->descriptionMd = $descriptionMd;

        return $this;
    }

    public function getHigherLevelsMd(): ?string
    {
        return $this->higherLevelsMd;
    }

    public function setHigherLevelsMd(?string $higherLevelsMd): static
    {
        $this->higherLevelsMd = $higherLevelsMd;

        return $this;
    }

    /**
     * @return Collection<int, ClassDef>
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(ClassDef $class): static
    {
        if (!$this->classes->contains($class)) {
            $this->classes->add($class);
        }

        return $this;
    }

    public function removeClass(ClassDef $class): static
    {
        $this->classes->removeElement($class);

        return $this;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(?string $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getTargetRangeSort(): ?int
    {
        return $this->targetRangeSort;
    }

    public function setTargetRangeSort(?int $targetRangeSort): static
    {
        $this->targetRangeSort = $targetRangeSort;
        return $this;
    }

    public function getComponents(): ?string
    {
        return $this->components;
    }

    public function setComponents(?string $components): static
    {
        $this->components = $components;
        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(?string $material): static
    {
        $this->material = $material;
        return $this;
    }

    public function isRitual(): ?bool
    {
        return $this->isRitual;
    }

    public function setIsRitual(?bool $isRitual): static
    {
        $this->isRitual = $isRitual;
        return $this;
    }

    public function isConcentration(): ?bool
    {
        return $this->isConcentration;
    }

    public function setIsConcentration(?bool $isConcentration): static
    {
        $this->isConcentration = $isConcentration;
        return $this;
    }

    public function isVerbal(): ?bool
    {
        return $this->isVerbal;
    }

    public function setIsVerbal(?bool $isVerbal): static
    {
        $this->isVerbal = $isVerbal;
        return $this;
    }

    public function isSomatic(): ?bool
    {
        return $this->isSomatic;
    }

    public function setIsSomatic(?bool $isSomatic): static
    {
        $this->isSomatic = $isSomatic;
        return $this;
    }

    public function isMaterial(): ?bool
    {
        return $this->isMaterial;
    }

    public function setIsMaterial(?bool $isMaterial): static
    {
        $this->isMaterial = $isMaterial;
        return $this;
    }

    public function getArchetype(): ?string
    {
        return $this->archetype;
    }

    public function setArchetype(?string $archetype): static
    {
        $this->archetype = $archetype;
        return $this;
    }

    public function getCircles(): ?string
    {
        return $this->circles;
    }

    public function setCircles(?string $circles): static
    {
        $this->circles = $circles;
        return $this;
    }
}
