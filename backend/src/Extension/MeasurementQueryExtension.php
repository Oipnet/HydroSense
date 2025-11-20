<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Measurement;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Query extension to filter measurements based on user ownership.
 * 
 * Ensures that users can only access measurements from reservoirs 
 * that belong to farms they own.
 * 
 * Security rule: measurement.reservoir.farm.owner == user
 * 
 * Admins can access all measurements.
 * 
 * @see FarmQueryExtension
 * @see ReservoirQueryExtension
 */
final class MeasurementQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    /**
     * Apply filtering to collection queries (GET /api/measurements).
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
     * Apply filtering to item queries (GET /api/measurements/{id}).
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
     * Joins: measurement -> reservoir -> farm -> owner
     * 
     * @param QueryBuilder $queryBuilder The query builder to modify
     * @param string $resourceClass The resource class name
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== Measurement::class) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        // Admins can access all measurements
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
