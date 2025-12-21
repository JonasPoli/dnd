<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\MagicItem;
use App\Repository\ExternalReferenceRepository;
use App\Repository\MagicItemRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class MagicItemImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private MagicItemRepository $magicItemRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'magicitem';
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
            'type' => $raw['type'] ?? null,
            'rarity' => $raw['rarity'] ?? null,
            'requiresAttunement' => $raw['requires_attunement'] ?? null,
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

        $item = null;
        if ($ref) {
            $item = $this->magicItemRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$item) {
            $item = new MagicItem();
            $item->setRulesSource($ctx->getRulesSource());
            $item->setRuleSlug($record->getExternalId());
            $this->entityManager->persist($item);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $item->setName($payload['name']);
        $item->setType($payload['type']);
        $item->setRarity($payload['rarity']);
        $item->setRequiresAttunement($payload['requiresAttunement']);
        $item->setDescriptionMd($payload['description']);
        $item->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush();
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($item->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $item->getId();
    }
}
