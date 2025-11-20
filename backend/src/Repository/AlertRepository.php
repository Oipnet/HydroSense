<?php

namespace App\Repository;

use App\Entity\Alert;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for Alert entity.
 * Provides custom queries for retrieving alerts.
 * 
 * @extends ServiceEntityRepository<Alert>
 */
class AlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alert::class);
    }

    /**
     * Find all unresolved alerts for a specific user.
     * 
     * @param User $user The user who owns the reservoirs
     * @return Alert[] Returns an array of unresolved Alert objects
     */
    public function findUnresolvedForUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.reservoir', 'r')
            ->innerJoin('r.farm', 'f')
            ->where('f.owner = :user')
            ->andWhere('a.resolvedAt IS NULL')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all alerts for a specific reservoir.
     * 
     * @param int $reservoirId The reservoir ID
     * @return Alert[] Returns an array of Alert objects
     */
    public function findByReservoir(int $reservoirId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.reservoir = :reservoirId')
            ->setParameter('reservoirId', $reservoirId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find alerts by type and severity for a specific user.
     * 
     * @param User $user The user who owns the reservoirs
     * @param string $type Alert type (PH_OUT_OF_RANGE, EC_OUT_OF_RANGE, TEMP_OUT_OF_RANGE)
     * @param string $severity Severity level (INFO, WARN, CRITICAL)
     * @return Alert[] Returns an array of Alert objects
     */
    public function findByTypeAndSeverityForUser(User $user, string $type, string $severity): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.reservoir', 'r')
            ->innerJoin('r.farm', 'f')
            ->where('f.owner = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.severity = :severity')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('severity', $severity)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count unresolved critical alerts for a specific user.
     * 
     * @param User $user The user who owns the reservoirs
     * @return int Number of unresolved critical alerts
     */
    public function countUnresolvedCriticalForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->innerJoin('a.reservoir', 'r')
            ->innerJoin('r.farm', 'f')
            ->where('f.owner = :user')
            ->andWhere('a.resolvedAt IS NULL')
            ->andWhere('a.severity = :severity')
            ->setParameter('user', $user)
            ->setParameter('severity', Alert::SEVERITY_CRITICAL)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
