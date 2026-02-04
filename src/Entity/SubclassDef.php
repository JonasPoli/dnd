<?php

namespace App\Entity;

use App\Repository\SubclassDefRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubclassDefRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_CLASS_KEY', fields: ['classDef', 'ruleSlug'])]
#[UniqueEntity(
    fields: ['classDef', 'ruleSlug'],
    errorPath: 'ruleSlug',
    message: 'Já existe uma subclasse com este slug nesta classe principal.'
)]
class SubclassDef
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?RulesSource $rulesSource = null;

    #[ORM\ManyToOne(inversedBy: 'subclasses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClassDef $classDef = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "O slug é obrigatório")]
    #[Assert\Regex(pattern: "/^[a-z0-9-]+$/", message: "O slug deve conter apenas letras minúsculas, números e hífens")]
    private ?string $ruleSlug = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "O nome é obrigatório")]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "O nível disponível é obrigatório")]
    #[Assert\Range(min: 1, max: 20)]
    private ?int $availableFromLevel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

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

    public function getClassDef(): ?ClassDef
    {
        return $this->classDef;
    }

    public function setClassDef(?ClassDef $classDef): static
    {
        $this->classDef = $classDef;

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

    public function getAvailableFromLevel(): ?int
    {
        return $this->availableFromLevel;
    }

    public function setAvailableFromLevel(int $availableFromLevel): static
    {
        $this->availableFromLevel = $availableFromLevel;

        return $this;
    }

    /**
     * @var Collection<int, SubclassSpell>
     */
    #[ORM\OneToMany(targetEntity: SubclassSpell::class, mappedBy: 'subclassDef', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $subclassSpells;

    public function __construct()
    {
        $this->subclassSpells = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * @return Collection<int, SubclassSpell>
     */
    public function getSubclassSpells(): Collection
    {
        return $this->subclassSpells;
    }

    public function addSubclassSpell(SubclassSpell $subclassSpell): static
    {
        if (!$this->subclassSpells->contains($subclassSpell)) {
            $this->subclassSpells->add($subclassSpell);
            $subclassSpell->setSubclassDef($this);
        }

        return $this;
    }

    public function removeSubclassSpell(SubclassSpell $subclassSpell): static
    {
        if ($this->subclassSpells->removeElement($subclassSpell)) {
            // set the owning side to null (unless already changed)
            if ($subclassSpell->getSubclassDef() === $this) {
                $subclassSpell->setSubclassDef(null);
            }
        }

        return $this;
    }
}
