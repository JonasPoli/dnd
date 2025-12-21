<?php

namespace App\Service\Import\Importer;

use App\Entity\Condition;
use App\Entity\ExternalReference;
use App\Repository\ConditionRepository;
use App\Repository\ExternalReferenceRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class ConditionImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private ConditionRepository $conditionRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'condition';
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

        $condition = null;
        if ($ref) {
            $condition = $this->conditionRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$condition) {
            $condition = new Condition();
            $condition->setRulesSource($ctx->getRulesSource());
            $condition->setRuleSlug($record->getExternalId());
            $this->entityManager->persist($condition);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $condition->setName($payload['name']);
        $condition->setDescriptionMd($payload['description']);
        $condition->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($condition->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $condition->getId();
    }
}
