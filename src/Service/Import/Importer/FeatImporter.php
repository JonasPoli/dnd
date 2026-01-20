<?php

namespace App\Service\Import\Importer;

use App\Entity\Feat;
use App\Entity\ExternalReference;
use App\Repository\FeatRepository;
use App\Repository\ExternalReferenceRepository;
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
        $desc = $raw['desc'] ?? '';
        
        // Append effects if present
        if (!empty($raw['effects_desc']) && is_array($raw['effects_desc'])) {
            $desc .= "\n\n";
            foreach ($raw['effects_desc'] as $effect) {
                $desc .= "- " . $effect . "\n";
            }
        }

        $payload = [
            'name' => $raw['name'],
            'description' => $desc,
            'prerequisite' => $raw['prerequisite'] ?? null,
            'type' => null, // Open5e doesn't seem to have a strict 'type' field in the root, maybe inference?
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
            // Try to find by name to avoid duplicates if seeded manually?
            // User warned about duplication/coexistence.
            // Safe approach: check by slug logic if possible, or usually we stick to external_reference source of truth.
            // If we want to link valid "English" imports to existing "Portuguese" ones, we'd need a name match or manual link.
            // Given the task is just "import because missing", we create new ones.
            
            $feat = new Feat();
            // Feat entity doesn't have RulesSource relation directly shown in Entity dump, 
            // but usually we rely on ExternalReference to link back.
            // Checking Feat.php dump again...
            // It ONLY has: id, isActive, name, type, prerequisite, descriptionMd.
            // It DOES NOT have rulesSource or ruleSlug columns mapped in the Entity file provided earlier!
            // Wait, ConditionImporter sets setRulesSource/setRuleSlug on Condition entity.
            // Does Feat entity have those?
            // Viewing Feat.php in step 61 shows NO rulesSource/ruleSlug.
            
            $this->entityManager->persist($feat);
            $ctx->addStats($this->getEntityType(), 'inserted');
        }

        $payload = $record->getPayload();
        $feat->setName($payload['name']); // This might conflict if unique constraint on Name exists.
        $feat->setDescriptionMd($payload['description']);
        $feat->setPrerequisite($payload['prerequisite']);
        $feat->setType($payload['type']);
        $feat->setIsActive(true);

        if (!$ref) {
            $this->entityManager->flush(); // Need ID for ref
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
