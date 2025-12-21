<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_KEY', fields: ['rulesSource', 'ruleSlug'])]
class Equipment
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

    #[ORM\Column(length: 50)]
    private ?string $type = null; // weapon, armor, gear, tool

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $costGp = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $weightLb = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $propertiesJson = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $damageDice = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $damageType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $weaponRange = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $weaponCategory = null;

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
}
