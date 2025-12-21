<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\Background;
use App\Repository\ExternalReferenceRepository;
use App\Repository\BackgroundRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class BackgroundImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private BackgroundRepository $backgroundRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'backgrounds';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
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

        $background = null;
        if ($ref) {
            $background = $this->backgroundRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$background) {
            $background = new Background();
            $background->setRulesSource($ctx->getRulesSource());
            $background->setRuleSlug($record->getExternalId());

            $this->entityManager->persist($background);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $background->setName($payload['name']);
        $background->setDescriptionMd($payload['description']);
        $background->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($background->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $background->getId();
    }
}
