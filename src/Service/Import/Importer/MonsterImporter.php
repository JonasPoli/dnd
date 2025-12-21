<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Monster;
use App\Repository\ExternalReferenceRepository;
use App\Repository\MonsterRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class MonsterImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private MonsterRepository $monsterRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'monster';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
            'size' => $raw['size'] ?? null,
            'type' => $raw['type'] ?? null,
            'subtype' => $raw['subtype'] ?? null,
            'group' => $raw['group'] ?? null,
            'alignment' => $raw['alignment'] ?? null,
            'challengeRating' => (string) ($raw['challenge_rating'] ?? ''),
            'cr' => (string) ($raw['cr'] ?? ''),
            'armorClass' => (int) ($raw['armor_class'] ?? 0),
            'armorDesc' => $raw['armor_desc'] ?? null,
            'hitPoints' => (int) ($raw['hit_points'] ?? 0),
            'hitDice' => $raw['hit_dice'] ?? null,
            'strength' => (int) ($raw['strength'] ?? 0),
            'dexterity' => (int) ($raw['dexterity'] ?? 0),
            'constitution' => (int) ($raw['constitution'] ?? 0),
            'intelligence' => (int) ($raw['intelligence'] ?? 0),
            'wisdom' => (int) ($raw['wisdom'] ?? 0),
            'charisma' => (int) ($raw['charisma'] ?? 0),
            'strengthSave' => isset($raw['strength_save']) ? (int) $raw['strength_save'] : null,
            'dexteritySave' => isset($raw['dexterity_save']) ? (int) $raw['dexterity_save'] : null,
            'constitutionSave' => isset($raw['constitution_save']) ? (int) $raw['constitution_save'] : null,
            'intelligenceSave' => isset($raw['intelligence_save']) ? (int) $raw['intelligence_save'] : null,
            'wisdomSave' => isset($raw['wisdom_save']) ? (int) $raw['wisdom_save'] : null,
            'charismaSave' => isset($raw['charisma_save']) ? (int) $raw['charisma_save'] : null,
            'perception' => isset($raw['perception']) ? (int) $raw['perception'] : null,
            'speed' => $raw['speed'] ?? [],
            'skills' => $raw['skills'] ?? [],
            'senses' => $raw['senses'] ?? null,
            'languages' => $raw['languages'] ?? null,
            'damageImmunities' => $raw['damage_immunities'] ?? null,
            'damageResistances' => $raw['damage_resistances'] ?? null,
            'damageVulnerabilities' => $raw['damage_vulnerabilities'] ?? null,
            'conditionImmunities' => $raw['condition_immunities'] ?? null,
            'legendaryDesc' => $raw['legendary_desc'] ?? null,
            'specialAbilities' => $raw['special_abilities'] ?? [],
            'actions' => $raw['actions'] ?? [],
            'bonusActions' => $raw['bonus_actions'] ?? [],
            'reactions' => $raw['reactions'] ?? [],
            'legendaryActions' => $raw['legendary_actions'] ?? [],
            'spellList' => $raw['spell_list'] ?? [],
            'pageNo' => isset($raw['page_no']) ? (int) $raw['page_no'] : null,
            'environments' => $raw['environments'] ?? [],
            'imgMain' => $raw['img_main'] ?? null,
            'description' => $raw['desc'] ?? '',
        ];

        return new NormalizedRecord(
            $this->getEntityType(),
            $raw['slug'],
            $payload
        );
    }

    public function upsert(NormalizedRecord $record, ImportContext $ctx): ?int
    {
        $hash = $this->hasher->hashNormalized($record);
        $ref = $this->externalRefRepo->findOneBySourceTypeAndExtId(
            $ctx->getRulesSource(),
            $this->getEntityType(),
            $record->getExternalId()
        );

        if ($ref && $ctx->isOnlyChanged() && $ref->getContentHash() === $hash) {
            $ctx->addStats($this->getEntityType(), 'skipped');
            return $ref->getLocalEntityId();
        }

        $monster = null;
        if ($ref) {
            $monster = $this->monsterRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$monster) {
            $monster = new Monster();
            $monster->setRulesSource($ctx->getRulesSource());
            $monster->setRuleSlug($record->getExternalId());
            $this->entityManager->persist($monster);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $monster->setName($payload['name']);
        $monster->setSize($payload['size']);
        $monster->setType($payload['type']);
        $monster->setSubtype($payload['subtype']);
        $monster->setGroup($payload['group']);
        $monster->setAlignment($payload['alignment']);
        $monster->setChallengeRating($payload['challengeRating']);
        $monster->setCr($payload['cr']);
        $monster->setArmorClass($payload['armorClass']);
        $monster->setArmorDesc($payload['armorDesc']);
        $monster->setHitPoints($payload['hitPoints']);
        $monster->setHitDice($payload['hitDice']);
        $monster->setStrength($payload['strength']);
        $monster->setDexterity($payload['dexterity']);
        $monster->setConstitution($payload['constitution']);
        $monster->setIntelligence($payload['intelligence']);
        $monster->setWisdom($payload['wisdom']);
        $monster->setCharisma($payload['charisma']);
        $monster->setStrengthSave($payload['strengthSave']);
        $monster->setDexteritySave($payload['dexteritySave']);
        $monster->setConstitutionSave($payload['constitutionSave']);
        $monster->setIntelligenceSave($payload['intelligenceSave']);
        $monster->setWisdomSave($payload['wisdomSave']);
        $monster->setCharismaSave($payload['charismaSave']);
        $monster->setPerception($payload['perception']);
        $monster->setSpeedJson($payload['speed']);
        $monster->setSkillsJson($payload['skills']);
        $monster->setSenses($payload['senses']);
        $monster->setLanguages($payload['languages']);
        $monster->setDamageImmunities($payload['damageImmunities']);
        $monster->setDamageResistances($payload['damageResistances']);
        $monster->setDamageVulnerabilities($payload['damageVulnerabilities']);
        $monster->setConditionImmunities($payload['conditionImmunities']);
        $monster->setLegendaryDesc($payload['legendaryDesc']);
        $monster->setSpecialAbilities($payload['specialAbilities']);
        $monster->setActionsJson($payload['actions']);
        $monster->setBonusActionsJson($payload['bonusActions']);
        $monster->setReactionsJson($payload['reactions']);
        $monster->setLegendaryActions($payload['legendaryActions']);
        $monster->setSpellList($payload['spellList']);
        $monster->setPageNo($payload['pageNo']);
        $monster->setEnvironments($payload['environments']);
        $monster->setImgMain($payload['imgMain']);
        $monster->setDescriptionMd($payload['description']);
        $monster->setSrcJson($payload);
        $monster->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($monster->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $monster->getId();
    }
}
