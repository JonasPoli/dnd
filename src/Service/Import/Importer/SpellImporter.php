<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Spell;
use App\Repository\ExternalReferenceRepository;
use App\Repository\SpellRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class SpellImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private SpellRepository $spellRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'spell';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        // Open5e spell structure mapping
        $payload = [
            'name' => $raw['name'],
            'level' => (int) ($raw['level_int'] ?? 0),
            'school' => $raw['school'] ?? 'Unknown',
            'castingTime' => $raw['casting_time'] ?? '',
            'range' => $raw['range'] ?? '',
            'components' => $raw['components'] ?? '',
            'duration' => $raw['duration'] ?? '',
            'description' => $raw['desc'] ?? '',
            'higherLevels' => $raw['higher_level'] ?? '',
            'page' => $raw['page'] ?? null,
            'targetRangeSort' => isset($raw['target_range_sort']) ? (int) $raw['target_range_sort'] : null,
            'material' => $raw['material'] ?? null,
            'isRitual' => $raw['can_be_cast_as_ritual'] ?? false,
            'isConcentration' => $raw['requires_concentration'] ?? false,
            'isVerbal' => $raw['requires_verbal_components'] ?? false,
            'isSomatic' => $raw['requires_somatic_components'] ?? false,
            'isMaterial' => $raw['requires_material_components'] ?? false,
            'archetype' => $raw['archetype'] ?? null,
            'circles' => $raw['circles'] ?? null,
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

        $spell = null;
        if ($ref) {
            $spell = $this->spellRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$spell) {
            $spell = new Spell();
            $spell->setRulesSource($ctx->getRulesSource());
            $spell->setRuleSlug($record->getExternalId());

            $this->entityManager->persist($spell);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $spell->setName($payload['name']);
        $spell->setLevel($payload['level']);
        $spell->setSchool($payload['school']);
        $spell->setCastingTime($payload['castingTime']);
        $spell->setSpellRange($payload['range']);
        $spell->setComponents($payload['components']); // Was componentsJson in Entity but raw uses components. Wait, entity has components (string) AND componentsJson. I used components string.

        $spell->setDuration($payload['duration']);
        $spell->setDescriptionMd($payload['description']);
        $spell->setHigherLevelsMd($payload['higherLevels']);

        $spell->setPage($payload['page']);
        $spell->setTargetRangeSort($payload['targetRangeSort']);
        $spell->setMaterial($payload['material']);
        $spell->setIsRitual($payload['isRitual']);
        $spell->setIsConcentration($payload['isConcentration']);
        $spell->setIsVerbal($payload['isVerbal']);
        $spell->setIsSomatic($payload['isSomatic']);
        $spell->setIsMaterial($payload['isMaterial']);
        $spell->setArchetype($payload['archetype']);
        $spell->setCircles($payload['circles']);

        $spell->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush(); // Get ID
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($spell->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $spell->getId();
    }
}
