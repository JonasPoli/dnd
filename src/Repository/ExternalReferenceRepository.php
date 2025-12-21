<?php

namespace App\Repository;

use App\Entity\ExternalReference;
use App\Entity\RulesSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExternalReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalReference::class);
    }

    public function findOneBySourceTypeAndExtId(RulesSource $source, string $entityType, string $externalId): ?ExternalReference
    {
        return $this->findOneBy([
            'rulesSource' => $source,
            'entityType' => $entityType,
            'externalId' => $externalId,
        ]);
    }
}
