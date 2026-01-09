<?php

namespace App\Repository;

use App\Entity\CharacterAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CharacterAttribute>
 *
 * @method CharacterAttribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method CharacterAttribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method CharacterAttribute[]    findAll()
 * @method CharacterAttribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharacterAttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterAttribute::class);
    }

    public function save(CharacterAttribute $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CharacterAttribute $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
