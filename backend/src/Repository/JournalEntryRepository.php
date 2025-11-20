<?php

namespace App\Repository;

use App\Entity\JournalEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JournalEntry>
 */
class JournalEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JournalEntry::class);
    }

    /**
     * Find all journal entries for a specific user.
     * 
     * @param int $userId The ID of the user
     * @return JournalEntry[] Returns an array of JournalEntry objects
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('j')
            ->innerJoin('j.reservoir', 'r')
            ->innerJoin('r.farm', 'f')
            ->innerJoin('f.owner', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all journal entries for a specific reservoir.
     * 
     * @param int $reservoirId The ID of the reservoir
     * @return JournalEntry[] Returns an array of JournalEntry objects
     */
    public function findByReservoir(int $reservoirId): array
    {
        return $this->createQueryBuilder('j')
            ->where('j.reservoir = :reservoirId')
            ->setParameter('reservoirId', $reservoirId)
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
