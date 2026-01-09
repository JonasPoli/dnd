<?php

namespace App\Repository;

use App\Entity\Feature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feature>
 */
class FeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feature::class);
    }

    public function findFeaturesForCharacter(\App\Entity\Character $character): array
    {
        $qb = $this->createQueryBuilder('f');
        $orX = $qb->expr()->orX();

        // Class Features
        if ($character->getClassDef()) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.ownerType', ':typeClass'),
                    $qb->expr()->eq('f.ownerId', ':idClass'),
                    $qb->expr()->lte('f.levelRequired', ':level')
                )
            );
            $qb->setParameter('typeClass', 'class');
            $qb->setParameter('idClass', $character->getClassDef()->getId());
        }

        // Subclass Features
        if ($character->getSubclassDef()) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.ownerType', ':typeSubclass'),
                    $qb->expr()->eq('f.ownerId', ':idSubclass'),
                    $qb->expr()->lte('f.levelRequired', ':level')
                )
            );
            $qb->setParameter('typeSubclass', 'subclass');
            $qb->setParameter('idSubclass', $character->getSubclassDef()->getId());
        }

        // Species Features
        if ($character->getSpecies()) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.ownerType', ':typeSpecies'),
                    $qb->expr()->eq('f.ownerId', ':idSpecies')
                )
            );
            $qb->setParameter('typeSpecies', 'species');
            $qb->setParameter('idSpecies', $character->getSpecies()->getId());
        }

        // Background Features
        if ($character->getBackground()) {
             $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.ownerType', ':typeBackground'),
                    $qb->expr()->eq('f.ownerId', ':idBackground')
                )
            );
            $qb->setParameter('typeBackground', 'background');
            $qb->setParameter('idBackground', $character->getBackground()->getId());
        }

        // Subrace Features
         if ($character->getSubrace()) {
             $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.ownerType', ':typeSubrace'),
                    $qb->expr()->eq('f.ownerId', ':idSubrace')
                )
            );
            $qb->setParameter('typeSubrace', 'subrace');
            $qb->setParameter('idSubrace', $character->getSubrace()->getId());
        }

        if ($orX->count() > 0) {
            $qb->where($orX);
            $qb->setParameter('level', $character->getLevel());
            
            // Order by Level then Name
            $qb->orderBy('f.levelRequired', 'ASC')
               ->addOrderBy('f.name', 'ASC');
               
            return $qb->getQuery()->getResult();
        }

        return [];
    }
}
