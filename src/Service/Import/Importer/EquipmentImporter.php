<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Equipment;
use App\Repository\ExternalReferenceRepository;
use App\Repository\EquipmentRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private EquipmentRepository $equipmentRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'equipment';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $costStr = $raw['cost'] ?? '';
        $costGp = 0.0;
        if (preg_match('/^([\d\.]+)\s*([a-z]+)$/i', $costStr, $m)) {
            $val = (float) $m[1];
            $unit = strtolower($m[2]);
            $costGp = match ($unit) {
                'cp' => $val / 100,
                'sp' => $val / 10,
                'ep' => $val / 2,
                'pp' => $val * 10,
                default => $val,
            };
        }

        $weightStr = $raw['weight'] ?? '';
        $weightLb = 0.0;
        if (preg_match('/^([\d\.]+)/', $weightStr, $m)) {
            $weightLb = (float) $m[1];
        }

        $properties = $raw['properties'] ?? [];
        $range = null;
        foreach ($properties as $prop) {
            if (preg_match('/range\s+([0-9\/]+)/', $prop, $m)) {
                $range = $m[1];
                break;
            }
        }

        $payload = [
            'name' => $raw['name'],
            'type' => 'weapon', // For now, defaulting to weapon as we import weapons.json
            'costGp' => $costGp,
            'weightLb' => $weightLb,
            'description' => $raw['desc'] ?? '',
            'damageDice' => $raw['damage_dice'] ?? null,
            'damageType' => $raw['damage_type'] ?? null,
            'weaponCategory' => $raw['category'] ?? null,
            'weaponRange' => $range,
            'properties' => $properties,
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

        $equipment = null;
        if ($ref) {
            $equipment = $this->equipmentRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$equipment) {
            $equipment = new Equipment();
            $equipment->setRulesSource($ctx->getRulesSource());
            $equipment->setRuleSlug($record->getExternalId());

            $this->entityManager->persist($equipment);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $equipment->setName($payload['name']);
        $equipment->setType($payload['type']);
        $equipment->setCostGp($payload['costGp']);
        $equipment->setWeightLb($payload['weightLb']);
        $equipment->setDescriptionMd($payload['description']);

        $equipment->setDamageDice($payload['damageDice']);
        $equipment->setDamageType($payload['damageType']);
        $equipment->setWeaponCategory($payload['weaponCategory']);
        $equipment->setWeaponRange($payload['weaponRange']);
        $equipment->setPropertiesJson($payload['properties']);

        $equipment->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($equipment->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $equipment->getId();
    }
}
