<?php

namespace App\Entity;

use App\Repository\BackgroundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackgroundRepository::class)]
class Background
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isActive = true;


    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\ManyToOne]
    private ?Attribute $attribute1 = null;

    #[ORM\ManyToOne]
    private ?Attribute $attribute2 = null;

    #[ORM\ManyToOne]
    private ?Attribute $attribute3 = null;

    #[ORM\ManyToOne]
    private ?Feat $feat = null;

    #[ORM\ManyToOne]
    private ?Skill $skill1 = null;

    #[ORM\ManyToOne]
    private ?Skill $skill2 = null;

    #[ORM\ManyToOne]
    private ?Equipment $toolProficiency = null;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\ManyToMany(targetEntity: Equipment::class)]
    #[ORM\JoinTable(name: 'background_equipment')]
    private Collection $startingEquipment;

    public function __construct()
    {
        $this->startingEquipment = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getAttribute1(): ?Attribute
    {
        return $this->attribute1;
    }

    public function setAttribute1(?Attribute $attribute1): static
    {
        $this->attribute1 = $attribute1;

        return $this;
    }

    public function getAttribute2(): ?Attribute
    {
        return $this->attribute2;
    }

    public function setAttribute2(?Attribute $attribute2): static
    {
        $this->attribute2 = $attribute2;

        return $this;
    }

    public function getAttribute3(): ?Attribute
    {
        return $this->attribute3;
    }

    public function setAttribute3(?Attribute $attribute3): static
    {
        $this->attribute3 = $attribute3;

        return $this;
    }

    public function getFeat(): ?Feat
    {
        return $this->feat;
    }

    public function setFeat(?Feat $feat): static
    {
        $this->feat = $feat;

        return $this;
    }

    public function getSkill1(): ?Skill
    {
        return $this->skill1;
    }

    public function setSkill1(?Skill $skill1): static
    {
        $this->skill1 = $skill1;

        return $this;
    }

    public function getSkill2(): ?Skill
    {
        return $this->skill2;
    }

    public function setSkill2(?Skill $skill2): static
    {
        $this->skill2 = $skill2;

        return $this;
    }

    public function getToolProficiency(): ?Equipment
    {
        return $this->toolProficiency;
    }

    public function setToolProficiency(?Equipment $toolProficiency): static
    {
        $this->toolProficiency = $toolProficiency;

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getStartingEquipment(): Collection
    {
        return $this->startingEquipment;
    }

    public function addStartingEquipment(Equipment $startingEquipment): static
    {
        if (!$this->startingEquipment->contains($startingEquipment)) {
            $this->startingEquipment->add($startingEquipment);
        }

        return $this;
    }

    public function removeStartingEquipment(Equipment $startingEquipment): static
    {
        $this->startingEquipment->removeElement($startingEquipment);

        return $this;
    }
}
