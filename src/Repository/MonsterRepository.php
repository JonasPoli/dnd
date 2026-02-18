<?php

namespace App\Repository;

use App\Entity\Monster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Monster>
 */
class MonsterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Monster::class);
    }

    /**
     * @return string[]
     */
    public function getDistinctValues(string $field): array
    {
        // Allow-list for security to prevent arbitrary DQL injection
        $allowedFields = [
            'type',
            'typePt',
            'subtype',
            'subtypePt',
            'monsterGroup',
            'monsterGroupPt', // Entity property names
            'size',
            'sizePt',
            'alignment',
            'alignmentPt'
        ];

        // Map form field names to entity property names if needed
        if ($field === 'group')
            $field = 'monsterGroup'; // Form uses 'group', entity uses 'monsterGroup' (based on mapping)
        if ($field === 'groupPt')
            $field = 'groupPt'; // Entity likely uses groupPt based on view_file output

        // Double check entity properties from previous view:
        // private ?string $group = null; // Wait, previous view showed:
        // 47:     #[ORM\Column(name: 'monster_group', length: 100, nullable: true)]
        // 48:     private ?string $group = null;
        // So the PROPERTY is $group, but the column is monster_group.
        // DQL uses PROPERTY names. So 'group' is correct for DQL 'm.group'.

        // Let's re-verify allowedFields.
        // Entity has: type, typePt, subtype, subtypePt, group, groupPt, size, sizePt, alignment, alignmentPt.

        $realBucket = [
            'type',
            'typePt',
            'subtype',
            'subtypePt',
            'group',
            'groupPt',
            'size',
            'sizePt',
            'alignment',
            'alignmentPt'
        ];

        if (!in_array($field, $realBucket)) {
            // If the field isn't in our allowed list, return empty or throw.
            // For safety, let's return []
            return [];
        }

        $qb = $this->createQueryBuilder('m')
            ->select("DISTINCT m.$field")
            ->where("m.$field IS NOT NULL")
            ->andWhere("m.$field != ''")
            ->orderBy("m.$field", 'ASC');

        $result = $qb->getQuery()->getSingleColumnResult();

        if (empty($result)) {
            return [];
        }

        return array_combine($result, $result);
    }
}
