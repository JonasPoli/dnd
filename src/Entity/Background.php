<?php

namespace App\Entity;

use App\Repository\BackgroundRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackgroundRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_KEY', fields: ['rulesSource', 'ruleSlug'])]
class Background
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $grantsJson = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $skillProficiencies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $toolProficiencies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $languages = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $equipment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $feature = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $featureDesc = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $suggestedCharacteristics = null;

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

    public function getDescriptionMd(): ?string
    {
        return $this->descriptionMd;
    }

    public function setDescriptionMd(?string $descriptionMd): static
    {
        $this->descriptionMd = $descriptionMd;

        return $this;
    }

    public function getGrantsJson(): ?array
    {
        return $this->grantsJson;
    }

    public function setGrantsJson(?array $grantsJson): static
    {
        $this->grantsJson = $grantsJson;

        return $this;
    }

    public function getSkillProficiencies(): ?string
    {
        return $this->skillProficiencies;
    }

    public function setSkillProficiencies(?string $skillProficiencies): static
    {
        $this->skillProficiencies = $skillProficiencies;
        return $this;
    }

    public function getToolProficiencies(): ?string
    {
        return $this->toolProficiencies;
    }

    public function setToolProficiencies(?string $toolProficiencies): static
    {
        $this->toolProficiencies = $toolProficiencies;
        return $this;
    }

    public function getLanguages(): ?string
    {
        return $this->languages;
    }

    public function setLanguages(?string $languages): static
    {
        $this->languages = $languages;
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

    public function getFeature(): ?string
    {
        return $this->feature;
    }

    public function setFeature(?string $feature): static
    {
        $this->feature = $feature;
        return $this;
    }

    public function getFeatureDesc(): ?string
    {
        return $this->featureDesc;
    }

    public function setFeatureDesc(?string $featureDesc): static
    {
        $this->featureDesc = $featureDesc;
        return $this;
    }

    public function getSuggestedCharacteristics(): ?string
    {
        return $this->suggestedCharacteristics;
    }

    public function setSuggestedCharacteristics(?string $suggestedCharacteristics): static
    {
        $this->suggestedCharacteristics = $suggestedCharacteristics;
        return $this;
    }
}
