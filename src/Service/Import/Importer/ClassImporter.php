<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\ClassDef;
use App\Repository\ExternalReferenceRepository;
use App\Repository\ClassDefRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\Importer\ImporterInterface;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class ClassImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private ClassDefRepository $classDefRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'classes';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $hitDiceVal = 8;
        if (preg_match('/1d(\d+)/', $raw['hit_dice'] ?? '', $m)) {
            $hitDiceVal = (int) $m[1];
        }

        $savingThrows = array_map('trim', explode(',', $raw['prof_saving_throws'] ?? ''));
        if (count($savingThrows) === 1 && empty($savingThrows[0])) {
            $savingThrows = [];
        }

        $payload = [
            'name' => $raw['name'],
            'hitDie' => $hitDiceVal,
            'description' => $raw['desc'] ?? '',
            'hpAt1stLevel' => $raw['hp_at_1st_level'] ?? null,
            'hpAtHigherLevels' => $raw['hp_at_higher_levels'] ?? null,
            'profArmor' => $raw['prof_armor'] ?? null,
            'profWeapons' => $raw['prof_weapons'] ?? null,
            'profTools' => $raw['prof_tools'] ?? null,
            'profSkills' => $raw['prof_skills'] ?? null,
            'equipment' => $raw['equipment'] ?? null,
            'classTableMd' => $raw['table'] ?? null,
            'spellcastingAbility' => $raw['spellcasting_ability'] ?? null,
            'subtypesName' => $raw['subtypes_name'] ?? null,
            'savingThrowProficiencies' => $savingThrows,
        ];

        return new NormalizedRecord($this->getEntityType(), $raw['slug'], $payload);
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

        $class = null;
        if ($ref) {
            $class = $this->classDefRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$class) {
            $class = new ClassDef();
            $class->setRulesSource($ctx->getRulesSource());
            $class->setRuleSlug($record->getExternalId());

            $this->entityManager->persist($class);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $class->setName($payload['name']);
        $class->setHitDie($payload['hitDie']);
        $class->setDescriptionMd($payload['description']);

        $class->setHpAt1stLevel($payload['hpAt1stLevel']);
        $class->setHpAtHigherLevels($payload['hpAtHigherLevels']);
        $class->setProfArmor($payload['profArmor']);
        $class->setProfWeapons($payload['profWeapons']);
        $class->setProfTools($payload['profTools']);
        $class->setProfSkills($payload['profSkills']);
        $class->setEquipment($payload['equipment']);
        $class->setClassTableMd($payload['classTableMd']);
        $class->setSpellcastingAbility($payload['spellcastingAbility']);
        $class->setSubtypesName($payload['subtypesName']);
        $class->setSavingThrowProficiencies($payload['savingThrowProficiencies']);

        $class->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($class->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $class->getId();
    }
}
