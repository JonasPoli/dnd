<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Subrace;
use App\Repository\ExternalReferenceRepository;
use App\Repository\SubraceRepository;
use App\Repository\SpeciesRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class SubraceImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private SubraceRepository $subraceRepo,
        private SpeciesRepository $speciesRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'subrace';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
            'description' => $raw['desc'] ?? '',
            'speciesSlug' => $raw['species_slug'],
            'asiDescription' => $raw['asi_desc'] ?? null,
            'asi' => $raw['asi'] ?? [],
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

        $subrace = null;
        if ($ref) {
            $subrace = $this->subraceRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$subrace) {
            $subrace = new Subrace();
            $subrace->setRulesSource($ctx->getRulesSource());
            $subrace->setRuleSlug($record->getExternalId());
            $this->entityManager->persist($subrace);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $subrace->setName($payload['name']);
        $subrace->setDescriptionMd($payload['description']);
        $subrace->setAsiDescription($payload['asiDescription']);
        $subrace->setAsi($payload['asi']);
        $subrace->setTraits($payload['traits']);

        // Resolve Species
        $species = $this->speciesRepo->findOneBy(['ruleSlug' => $payload['speciesSlug'], 'rulesSource' => $ctx->getRulesSource()]);

        if ($species) {
            $subrace->setSpecies($species);
        }

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($subrace->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $subrace->getId();
    }
}
