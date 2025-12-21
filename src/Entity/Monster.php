<?php

namespace App\Entity;

use App\Repository\MonsterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonsterRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_MONSTER', fields: ['rulesSource', 'ruleSlug'])]
class Monster
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

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $size = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $subtype = null;

    #[ORM\Column(name: 'monster_group', length: 100, nullable: true)]
    private ?string $group = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $alignment = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $challengeRating = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $cr = null;

    #[ORM\Column(nullable: true)]
    private ?int $armorClass = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $armorDesc = null;

    #[ORM\Column(nullable: true)]
    private ?int $hitPoints = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $hitDice = null;

    #[ORM\Column(nullable: true)]
    private ?int $strength = null;

    #[ORM\Column(nullable: true)]
    private ?int $dexterity = null;

    #[ORM\Column(nullable: true)]
    private ?int $constitution = null;

    #[ORM\Column(nullable: true)]
    private ?int $intelligence = null;

    #[ORM\Column(nullable: true)]
    private ?int $wisdom = null;

    #[ORM\Column(nullable: true)]
    private ?int $charisma = null;

    #[ORM\Column(nullable: true)]
    private ?int $strengthSave = null;

    #[ORM\Column(nullable: true)]
    private ?int $dexteritySave = null;

    #[ORM\Column(nullable: true)]
    private ?int $constitutionSave = null;

    #[ORM\Column(nullable: true)]
    private ?int $intelligenceSave = null;

    #[ORM\Column(nullable: true)]
    private ?int $wisdomSave = null;

    #[ORM\Column(nullable: true)]
    private ?int $charismaSave = null;

    #[ORM\Column(nullable: true)]
    private ?int $perception = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $speedJson = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $skillsJson = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $senses = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $languages = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $specialAbilitiesJson = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $actionsJson = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $bonusActionsJson = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $reactionsJson = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $legendaryActionsJson = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMd = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $damageImmunities = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $damageResistances = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $damageVulnerabilities = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $conditionImmunities = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $legendaryDesc = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $spellList = [];

    #[ORM\Column(nullable: true)]
    private ?int $pageNo = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $environments = [];

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imgMain = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $srcJson = [];

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

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
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

    public function getChallengeRating(): ?string
    {
        return $this->challengeRating;
    }

    public function setChallengeRating(?string $challengeRating): static
    {
        $this->challengeRating = $challengeRating;
        return $this;
    }

    public function getArmorClass(): ?int
    {
        return $this->armorClass;
    }

    public function setArmorClass(?int $armorClass): static
    {
        $this->armorClass = $armorClass;
        return $this;
    }

    public function getHitPoints(): ?int
    {
        return $this->hitPoints;
    }

    public function setHitPoints(?int $hitPoints): static
    {
        $this->hitPoints = $hitPoints;
        return $this;
    }

    public function getHitDice(): ?string
    {
        return $this->hitDice;
    }

    public function setHitDice(?string $hitDice): static
    {
        $this->hitDice = $hitDice;
        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(?int $strength): static
    {
        $this->strength = $strength;
        return $this;
    }

    public function getDexterity(): ?int
    {
        return $this->dexterity;
    }

    public function setDexterity(?int $dexterity): static
    {
        $this->dexterity = $dexterity;
        return $this;
    }

    public function getConstitution(): ?int
    {
        return $this->constitution;
    }

    public function setConstitution(?int $constitution): static
    {
        $this->constitution = $constitution;
        return $this;
    }

    public function getIntelligence(): ?int
    {
        return $this->intelligence;
    }

    public function setIntelligence(?int $intelligence): static
    {
        $this->intelligence = $intelligence;
        return $this;
    }

    public function getWisdom(): ?int
    {
        return $this->wisdom;
    }

    public function setWisdom(?int $wisdom): static
    {
        $this->wisdom = $wisdom;
        return $this;
    }

    public function getCharisma(): ?int
    {
        return $this->charisma;
    }

    public function setCharisma(?int $charisma): static
    {
        $this->charisma = $charisma;
        return $this;
    }

    public function getSpeedJson(): ?array
    {
        return $this->speedJson;
    }

    public function setSpeedJson(?array $speedJson): static
    {
        $this->speedJson = $speedJson;
        return $this;
    }

    public function getSkillsJson(): ?array
    {
        return $this->skillsJson;
    }

    public function setSkillsJson(?array $skillsJson): static
    {
        $this->skillsJson = $skillsJson;
        return $this;
    }

    public function getSenses(): ?string
    {
        return $this->senses;
    }

    public function setSenses(?string $senses): static
    {
        $this->senses = $senses;
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

    public function getSpecialAbilitiesJson(): ?array
    {
        return $this->specialAbilitiesJson;
    }

    public function setSpecialAbilities(?array $specialAbilitiesJson): static
    {
        $this->specialAbilitiesJson = $specialAbilitiesJson;
        return $this;
    }

    public function getActionsJson(): ?array
    {
        return $this->actionsJson;
    }

    public function setActionsJson(?array $actionsJson): static
    {
        $this->actionsJson = $actionsJson;
        return $this;
    }

    public function getLegendaryActionsJson(): ?array
    {
        return $this->legendaryActionsJson;
    }

    public function setLegendaryActions(?array $legendaryActionsJson): static
    {
        $this->legendaryActionsJson = $legendaryActionsJson;
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

    public function getSrcJson(): ?array
    {
        return $this->srcJson;
    }

    public function setSrcJson(?array $srcJson): static
    {
        $this->srcJson = $srcJson;
        return $this;
    }

    public function getDamageImmunities(): ?string
    {
        return $this->damageImmunities;
    }

    public function setDamageImmunities(?string $damageImmunities): static
    {
        $this->damageImmunities = $damageImmunities;
        return $this;
    }

    public function getDamageResistances(): ?string
    {
        return $this->damageResistances;
    }

    public function setDamageResistances(?string $damageResistances): static
    {
        $this->damageResistances = $damageResistances;
        return $this;
    }

    public function getDamageVulnerabilities(): ?string
    {
        return $this->damageVulnerabilities;
    }

    public function setDamageVulnerabilities(?string $damageVulnerabilities): static
    {
        $this->damageVulnerabilities = $damageVulnerabilities;
        return $this;
    }

    public function getConditionImmunities(): ?string
    {
        return $this->conditionImmunities;
    }

    public function setConditionImmunities(?string $conditionImmunities): static
    {
        $this->conditionImmunities = $conditionImmunities;
        return $this;
    }

    public function getLegendaryDesc(): ?string
    {
        return $this->legendaryDesc;
    }

    public function setLegendaryDesc(?string $legendaryDesc): static
    {
        $this->legendaryDesc = $legendaryDesc;
        return $this;
    }

    public function getSubtype(): ?string
    {
        return $this->subtype;
    }

    public function setSubtype(?string $subtype): static
    {
        $this->subtype = $subtype;
        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): static
    {
        $this->group = $group;
        return $this;
    }

    public function getArmorDesc(): ?string
    {
        return $this->armorDesc;
    }

    public function setArmorDesc(?string $armorDesc): static
    {
        $this->armorDesc = $armorDesc;
        return $this;
    }

    public function getCr(): ?string
    {
        return $this->cr;
    }

    public function setCr(?string $cr): static
    {
        $this->cr = $cr;
        return $this;
    }

    public function getStrengthSave(): ?int
    {
        return $this->strengthSave;
    }

    public function setStrengthSave(?int $strengthSave): static
    {
        $this->strengthSave = $strengthSave;
        return $this;
    }

    public function getDexteritySave(): ?int
    {
        return $this->dexteritySave;
    }

    public function setDexteritySave(?int $dexteritySave): static
    {
        $this->dexteritySave = $dexteritySave;
        return $this;
    }

    public function getConstitutionSave(): ?int
    {
        return $this->constitutionSave;
    }

    public function setConstitutionSave(?int $constitutionSave): static
    {
        $this->constitutionSave = $constitutionSave;
        return $this;
    }

    public function getIntelligenceSave(): ?int
    {
        return $this->intelligenceSave;
    }

    public function setIntelligenceSave(?int $intelligenceSave): static
    {
        $this->intelligenceSave = $intelligenceSave;
        return $this;
    }

    public function getWisdomSave(): ?int
    {
        return $this->wisdomSave;
    }

    public function setWisdomSave(?int $wisdomSave): static
    {
        $this->wisdomSave = $wisdomSave;
        return $this;
    }

    public function getCharismaSave(): ?int
    {
        return $this->charismaSave;
    }

    public function setCharismaSave(?int $charismaSave): static
    {
        $this->charismaSave = $charismaSave;
        return $this;
    }

    public function getPerception(): ?int
    {
        return $this->perception;
    }

    public function setPerception(?int $perception): static
    {
        $this->perception = $perception;
        return $this;
    }

    public function getBonusActionsJson(): ?array
    {
        return $this->bonusActionsJson;
    }

    public function setBonusActionsJson(?array $bonusActionsJson): static
    {
        $this->bonusActionsJson = $bonusActionsJson;
        return $this;
    }

    public function getReactionsJson(): ?array
    {
        return $this->reactionsJson;
    }

    public function setReactionsJson(?array $reactionsJson): static
    {
        $this->reactionsJson = $reactionsJson;
        return $this;
    }

    public function getSpellList(): ?array
    {
        return $this->spellList;
    }

    public function setSpellList(?array $spellList): static
    {
        $this->spellList = $spellList;
        return $this;
    }

    public function getPageNo(): ?int
    {
        return $this->pageNo;
    }

    public function setPageNo(?int $pageNo): static
    {
        $this->pageNo = $pageNo;
        return $this;
    }

    public function getEnvironments(): ?array
    {
        return $this->environments;
    }

    public function setEnvironments(?array $environments): static
    {
        $this->environments = $environments;
        return $this;
    }

    public function getImgMain(): ?string
    {
        return $this->imgMain;
    }

    public function setImgMain(?string $imgMain): static
    {
        $this->imgMain = $imgMain;
        return $this;
    }
}
