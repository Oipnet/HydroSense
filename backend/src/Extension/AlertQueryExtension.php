<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Alert;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Query extension to filter alerts based on user ownership.
 * 
 * Ensures that users can only access alerts from reservoirs 
 * that belong to farms they own.
 * 
 * Security rule: alert.reservoir.farm.owner == user
 * 
 * Admins can access all alerts.
 * 
 * This extension is automatically applied to all Alert queries
 * (both collection and item operations), ensuring data isolation
 * between users.
 * 
 * @see MeasurementQueryExtension Similar extension for measurements
 * @see ReservoirQueryExtension Similar extension for reservoirs
 */
final class AlertQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    /**
     * Apply filtering to collection queries (GET /api/alerts).
     * 
     * Joins through the ownership chain:
     * alert -> reservoir -> farm -> owner
     * 
     * @param QueryBuilder $queryBuilder The query builder to modify
     * @param QueryNameGeneratorInterface $queryNameGenerator Query name generator
     * @param string $resourceClass The resource class name
     * @param Operation|null $operation The current operation
     * @param array<string, mixed> $context Additional context
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Apply filtering to item queries (GET /api/alerts/{id}).
     * 
     * @param QueryBuilder $queryBuilder The query builder to modify
     * @param QueryNameGeneratorInterface $queryNameGenerator Query name generator
     * @param string $resourceClass The resource class name
     * @param array<string, mixed> $identifiers The item identifiers
     * @param Operation|null $operation The current operation
     * @param array<string, mixed> $context Additional context
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Add the ownership constraint to the query.
     * 
     * Joins: alert -> reservoir -> farm -> owner
     * Filters by current authenticated user
     * 
     * @param QueryBuilder $queryBuilder The query builder to modify
     * @param string $resourceClass The resource class name
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Only apply to Alert entities
        if ($resourceClass !== Alert::class) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        // Admins can access all alerts
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        
        // Join through reservoir -> farm -> owner to filter by ownership
        $queryBuilder
            ->innerJoin(sprintf('%s.reservoir', $rootAlias), 'r')
            ->innerJoin('r.farm', 'f')
            ->innerJoin('f.owner', 'u')
            ->andWhere('u.id = :current_user')
            ->setParameter('current_user', $user->getId());
    }
}
