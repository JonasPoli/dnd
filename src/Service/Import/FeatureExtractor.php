<?php

namespace App\Service\Import;

use App\Entity\Feature;
use App\Entity\RulesSource;
use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class FeatureExtractor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FeatureRepository $featureRepository,
        private SluggerInterface $slugger
    ) {
    }

    /**
     * @param array $featureData Must contain 'name' and 'description'. Optional: 'level', 'grants'.
     */
    public function extract(array $featureData, RulesSource $source, string $ownerType, int $ownerId, string $prefix = ''): void
    {
        $name = trim($featureData['name'] ?? '');
        if (empty($name)) {
            return;
        }

        // Generate a unique key for this feature within the context
        // We use ownerType + ownerId + slug(name) to try to find existing ones
        // But features might be shared? Ideally not for specific class features.
        // Let's scope it to the owner for now to avoid collisions, unless we want global features.
        // For import purposes, keeping it simple: Find by (RulesSource, OwnerType, OwnerId, Key)

        $slug = strtolower($this->slugger->slug($name)->toString());
        if ($prefix) {
            $key = $prefix . '-' . $slug;
        } else {
            $key = $slug;
        }

        $feature = $this->featureRepository->findOneBy([
            'rulesSource' => $source,
            'ownerType' => $ownerType,
            'ownerId' => $ownerId,
            'key' => $key
        ]);

        if (!$feature) {
            $feature = new Feature();
            $feature->setRulesSource($source);
            $feature->setOwnerType($ownerType);
            $feature->setOwnerId($ownerId);
            $feature->setKey($key);
        }

        $feature->setName($name);
        $feature->setDescriptionMd($featureData['description'] ?? '');
        $feature->setLevelRequired($featureData['level'] ?? null);

        if (isset($featureData['grants'])) {
            $feature->setGrantsJson($featureData['grants']);
        }

        $this->entityManager->persist($feature);
    }
}
