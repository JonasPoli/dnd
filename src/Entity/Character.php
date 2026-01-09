<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: '`character`')]
#[Vich\Uploadable]
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
    #[ORM\JoinColumn(nullable: true)]
    private ?ClassDef $classDef = null;

    #[ORM\ManyToOne]
    private ?SubclassDef $subclassDef = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Species $species = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Background $background = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Subrace $subrace = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $alignment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $appearance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bonds = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $origin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[Vich\UploadableField(mapping: 'character_image', fileNameProperty: 'imagePath')]
    private ?File $imageFile = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isComplete = false;

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

    #[ORM\ManyToMany(targetEntity: Language::class)]
    #[ORM\JoinTable(name: 'character_language')]
    private Collection $languages;

    #[ORM\ManyToMany(targetEntity: Equipment::class)]
    #[ORM\JoinTable(name: 'character_inventory')]
    private Collection $inventory;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $coinCp = 0;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $coinSp = 0;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $coinEp = 0;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $coinGp = 0;

    #[ORM\Column(options: ['default' => 0])]
    private ?int $coinPp = 0;

    #[ORM\OneToMany(targetEntity: CharacterAttribute::class, mappedBy: 'character', orphanRemoval: true)]
    private Collection $characterAttributes;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $attributeBonuses = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->characterSpells = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->toolProficiencies = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->inventory = new ArrayCollection();
        $this->characterAttributes = new ArrayCollection();
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

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        $this->languages->removeElement($language);

        return $this;
    }

    /**
     * @return Collection<int, CharacterAttribute>
     */
    public function getCharacterAttributes(): Collection
    {
        return $this->characterAttributes;
    }

    public function addCharacterAttribute(CharacterAttribute $characterAttribute): static
    {
        if (!$this->characterAttributes->contains($characterAttribute)) {
            $this->characterAttributes->add($characterAttribute);
            $characterAttribute->setCharacter($this);
        }

        return $this;
    }

    public function removeCharacterAttribute(CharacterAttribute $characterAttribute): static
    {
        if ($this->characterAttributes->removeElement($characterAttribute)) {
            // set the owning side to null (unless already changed)
            if ($characterAttribute->getCharacter() === $this) {
                $characterAttribute->setCharacter(null);
            }
        }

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

    public function getSubrace(): ?Subrace
    {
        return $this->subrace;
    }

    public function setSubrace(?Subrace $subrace): static
    {
        $this->subrace = $subrace;

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

    public function getAppearance(): ?string
    {
        return $this->appearance;
    }

    public function setAppearance(?string $appearance): static
    {
        $this->appearance = $appearance;

        return $this;
    }

    public function getBonds(): ?string
    {
        return $this->bonds;
    }

    public function setBonds(?string $bonds): static
    {
        $this->bonds = $bonds;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(?string $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function isComplete(): ?bool
    {
        return $this->isComplete;
    }

    public function setIsComplete(bool $isComplete): static
    {
        $this->isComplete = $isComplete;

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

    /**
     * @return Collection<int, Equipment>
     */
    public function getInventory(): Collection
    {
        return $this->inventory;
    }

    public function addInventoryItem(Equipment $item): static
    {
        if (!$this->inventory->contains($item)) {
            $this->inventory->add($item);
        }

        return $this;
    }

    public function removeInventoryItem(Equipment $item): static
    {
        $this->inventory->removeElement($item);

        return $this;
    }

    public function getCoinCp(): ?int
    {
        return $this->coinCp;
    }

    public function setCoinCp(int $coinCp): static
    {
        $this->coinCp = $coinCp;

        return $this;
    }

    public function getCoinSp(): ?int
    {
        return $this->coinSp;
    }

    public function setCoinSp(int $coinSp): static
    {
        $this->coinSp = $coinSp;

        return $this;
    }

    public function getCoinEp(): ?int
    {
        return $this->coinEp;
    }

    public function setCoinEp(int $coinEp): static
    {
        $this->coinEp = $coinEp;

        return $this;
    }

    public function getCoinGp(): ?int
    {
        return $this->coinGp;
    }

    public function setCoinGp(int $coinGp): static
    {
        $this->coinGp = $coinGp;

        return $this;
    }

    public function getCoinPp(): ?int
    {
        return $this->coinPp;
    }

    public function setCoinPp(int $coinPp): static
    {
        $this->coinPp = $coinPp;

        return $this;
    }
    public function getAttributeBonuses(): ?array
    {
        return $this->attributeBonuses;
    }

    public function setAttributeBonuses(?array $attributeBonuses): static
    {
        $this->attributeBonuses = $attributeBonuses;

        return $this;
    }
}
