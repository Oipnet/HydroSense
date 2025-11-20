<?php

namespace App\Repository;

use App\Entity\CultureProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CultureProfile>
 */
class CultureProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CultureProfile::class);
    }

    /**
     * Rechercher un profil de culture par nom
     *
     * @param string $name
     * @return CultureProfile|null
     */
    public function findByName(string $name): ?CultureProfile
    {
        return $this->createQueryBuilder('cp')
            ->andWhere('cp.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupérer tous les profils ordonnés par nom
     *
     * @return CultureProfile[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('cp')
            ->orderBy('cp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
