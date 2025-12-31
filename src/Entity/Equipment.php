<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isActive = true;






    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null; // weapon, armor, gear, tool

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $typePt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $costGp = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $weightLb = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $weightKg = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $propertiesJson = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMdPt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $damageDice = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $damageType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $damageTypePt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $weaponRange = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $weaponCategory = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $weaponCategoryPt = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCostGp(): ?string
    {
        return $this->costGp;
    }

    public function setCostGp(?string $costGp): static
    {
        $this->costGp = $costGp;

        return $this;
    }

    public function getWeightLb(): ?string
    {
        return $this->weightLb;
    }

    public function setWeightLb(?string $weightLb): static
    {
        $this->weightLb = $weightLb;

        return $this;
    }

    public function getWeightKg(): ?string
    {
        return $this->weightKg;
    }

    public function setWeightKg(?string $weightKg): static
    {
        $this->weightKg = $weightKg;

        return $this;
    }

    public function getPropertiesJson(): ?array
    {
        return $this->propertiesJson;
    }

    public function setPropertiesJson(?array $propertiesJson): static
    {
        $this->propertiesJson = $propertiesJson;

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

    public function getDamageDice(): ?string
    {
        return $this->damageDice;
    }

    public function setDamageDice(?string $damageDice): static
    {
        $this->damageDice = $damageDice;

        return $this;
    }

    public function getDamageType(): ?string
    {
        return $this->damageType;
    }

    public function setDamageType(?string $damageType): static
    {
        $this->damageType = $damageType;

        return $this;
    }

    public function getWeaponRange(): ?string
    {
        return $this->weaponRange;
    }

    public function setWeaponRange(?string $weaponRange): static
    {
        $this->weaponRange = $weaponRange;

        return $this;
    }

    public function getWeaponCategory(): ?string
    {
        return $this->weaponCategory;
    }

    public function setWeaponCategory(?string $weaponCategory): static
    {
        $this->weaponCategory = $weaponCategory;

        return $this;
    }

    public function getTypePt(): ?string
    {
        return $this->typePt;
    }

    public function setTypePt(?string $typePt): static
    {
        $this->typePt = $typePt;

        return $this;
    }

    public function getDescriptionMdPt(): ?string
    {
        return $this->descriptionMdPt;
    }

    public function setDescriptionMdPt(?string $descriptionMdPt): static
    {
        $this->descriptionMdPt = $descriptionMdPt;

        return $this;
    }

    public function getDamageTypePt(): ?string
    {
        return $this->damageTypePt;
    }

    public function setDamageTypePt(?string $damageTypePt): static
    {
        $this->damageTypePt = $damageTypePt;

        return $this;
    }

    public function getWeaponCategoryPt(): ?string
    {
        return $this->weaponCategoryPt;
    }

    public function setWeaponCategoryPt(?string $weaponCategoryPt): static
    {
        $this->weaponCategoryPt = $weaponCategoryPt;

        return $this;
    }
}
