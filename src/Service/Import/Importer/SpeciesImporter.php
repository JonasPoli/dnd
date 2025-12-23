<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Species;
use App\Repository\ExternalReferenceRepository;
use App\Repository\SpeciesRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\Importer\ImporterInterface;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class SpeciesImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private SpeciesRepository $speciesRepo,
        private Hasher $hasher,
        private \App\Service\Import\FeatureExtractor $featureExtractor
    ) {
    }

    public function getEntityType(): string
    {
        return 'species';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
            'size' => $raw['size'] ?? 'Medium',
            'speedWalk' => (int) ($raw['speed']['walk'] ?? 30),
            'description' => $raw['desc'] ?? '',
            'asiDescription' => $raw['asi_desc'] ?? null,
            'asi' => $raw['asi'] ?? [],
            'age' => $raw['age'] ?? null,
            'alignment' => $raw['alignment'] ?? null,
            'speedDescription' => $raw['speed_desc'] ?? null,
            'languages' => $raw['languages'] ?? null,
            'vision' => $raw['vision'] ?? null,
            'traits' => $raw['traits'] ?? null,
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

        $species = null;
        if ($ref) {
            $species = $this->speciesRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$species) {
            // Check if species already exists (crash recovery)
            $species = $this->speciesRepo->findOneBy([
                'rulesSource' => $ctx->getRulesSource(),
                'ruleSlug' => $record->getExternalId()
            ]);
        }

        if (!$species) {
            $species = new Species();
            $species->setRulesSource($ctx->getRulesSource());
            $species->setRuleSlug($record->getExternalId());

            $this->entityManager->persist($species);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $species->setName($payload['name']);
        $species->setSize($payload['size']);
        $species->setSpeedWalk($payload['speedWalk']);
        $species->setDescriptionMd($payload['description']);

        $species->setAsiDescription($payload['asiDescription']);
        $species->setAsi($payload['asi']);
        $species->setAge($payload['age']);
        $species->setAlignment($payload['alignment']);
        $species->setSpeedDescription($payload['speedDescription']);
        $species->setLanguages($payload['languages']);
        $species->setVision($payload['vision']);
        $species->setTraits($payload['traits']);

        $species->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($species->getId());
            $this->entityManager->persist($ref);
        } else {
            $this->entityManager->flush();
        }

        // Set these immediately to avoid "content_hash cannot be null" if flush happens later
        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        // --- Feature Extraction ---
        if (!empty($payload['traits'])) {
            $traits = $payload['traits'];
            if (is_string($traits)) {
                // If traits is a string, it might be a description or malformed.
                // We should probably skip iteration or try to parse if json.
                // For now, treat as single trait with description=string if it looks like text.
                 $traits = [['name' => 'Traits', 'description' => $traits]];
            }

            if (is_array($traits)) {
                foreach ($traits as $trait) {
                    // Ensure description is mapped correctly if it comes as 'desc'
                    if (!isset($trait['description']) && isset($trait['desc'])) {
                        $trait['description'] = $trait['desc'];
                    }

                    $this->featureExtractor->extract(
                        $trait,
                        $ctx->getRulesSource(),
                        'species',
                        $species->getId()
                    );
                }
            }
        }

        return $species->getId();
    }
}
