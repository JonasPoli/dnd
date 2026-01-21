<?php

namespace App\Entity;

use App\Repository\ConditionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConditionRepository::class)]
#[ORM\Table(name: 'rule_condition')]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_CONDITION', fields: ['rulesSource', 'ruleSlug'])]
class Condition
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $namePt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMdPt = null;

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

    public function getNamePt(): ?string
    {
        return $this->namePt;
    }

    public function setNamePt(?string $namePt): static
    {
        $this->namePt = $namePt;
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

    public function getDescriptionMdPt(): ?string
    {
        return $this->descriptionMdPt;
    }

    public function setDescriptionMdPt(?string $descriptionMdPt): static
    {
        $this->descriptionMdPt = $descriptionMdPt;
        return $this;
    }
}
