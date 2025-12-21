<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Feat;
use App\Repository\ExternalReferenceRepository;
use App\Repository\FeatRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class FeatImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private FeatRepository $featRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'feat';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
            'prerequisite' => $raw['prerequisite'] ?? null,
            'description' => $raw['desc'] ?? '',
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

        $feat = null;
        if ($ref) {
            $feat = $this->featRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$feat) {
            $feat = new Feat();
            $feat->setRulesSource($ctx->getRulesSource());
            $feat->setRuleSlug($record->getExternalId());
            $this->entityManager->persist($feat);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $feat->setName($payload['name']);
        $feat->setPrerequisite($payload['prerequisite']);
        $feat->setDescriptionMd($payload['description']);
        $feat->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($feat->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $feat->getId();
    }
}
