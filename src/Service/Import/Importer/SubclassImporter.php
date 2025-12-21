<?php

namespace App\Service\Import\Importer;

use App\Entity\ExternalReference;
use App\Entity\SubclassDef;
use App\Repository\ExternalReferenceRepository;
use App\Repository\SubclassDefRepository;
use App\Repository\ClassDefRepository;
use App\Service\Import\Hasher;
use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;
use Doctrine\ORM\EntityManagerInterface;

class SubclassImporter implements ImporterInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExternalReferenceRepository $externalRefRepo,
        private SubclassDefRepository $subclassRepo,
        private ClassDefRepository $classRepo,
        private Hasher $hasher
    ) {
    }

    public function getEntityType(): string
    {
        return 'subclass';
    }

    private function inferLevel(string $className): int
    {
        return match (strtolower($className)) {
            'cleric', 'sorcerer', 'warlock' => 1,
            'druid', 'wizard' => 2,
            default => 3, // Barbarian, Bard, Fighter, Monk, Paladin, Ranger, Rogue
        };
    }

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord
    {
        $payload = [
            'name' => $raw['name'],
            'description' => $raw['desc'] ?? '',
            'classSlug' => $raw['class_slug'],
            'className' => $raw['class_name'], // Used for level inference if not stored
            'availableFromLevel' => $this->inferLevel($raw['class_name']),
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

        $subclass = null;
        if ($ref) {
            $subclass = $this->subclassRepo->find($ref->getLocalEntityId());
            $ctx->addStats($this->getEntityType(), 'updated');
        }

        if (!$subclass) {
            $subclass = new SubclassDef();
            $subclass->setRulesSource($ctx->getRulesSource());
            $subclass->setRuleSlug($record->getExternalId());
            $this->entityManager->persist($subclass);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $subclass->setName($payload['name']);
        $subclass->setDescriptionMd($payload['description']);
        $subclass->setAvailableFromLevel($payload['availableFromLevel']);

        // Resolve Class Def
        // We know the class must exist if we imported classes first.
        // We can find it by slug or external reference.
        // But ClassDef entity stores its own ruleSlug which matches the import externalID for classes.
        $classDef = $this->classRepo->findOneBy(['ruleSlug' => $payload['classSlug'], 'rulesSource' => $ctx->getRulesSource()]);

        if ($classDef) {
            $subclass->setClassDef($classDef);
        } else {
            // Log warning or skip?
            // If class not found, we can't persist valid subclass.
            // But strict requirement?
        }

        if (!$ref) {
            $this->entityManager->flush(); // Need ID for ref
            $ref = new ExternalReference();
            $ref->setRulesSource($ctx->getRulesSource());
            $ref->setEntityType($this->getEntityType());
            $ref->setExternalId($record->getExternalId());
            $ref->setLocalEntityId($subclass->getId());
            $this->entityManager->persist($ref);
        }

        $ref->setContentHash($hash);
        $ref->setLastImportedAt(new \DateTimeImmutable());
        $ref->setStatus('active');

        return $subclass->getId();
    }
}
