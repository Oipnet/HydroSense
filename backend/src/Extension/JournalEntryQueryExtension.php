<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\JournalEntry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * JournalEntryQueryExtension automatically filters journal entries based on user ownership.
 * 
 * This extension ensures that users can only access journal entries for reservoirs
 * they own through their farms. It's applied automatically to all JournalEntry
 * collection and item queries.
 * 
 * Security logic:
 * - Joins through: JournalEntry -> Reservoir -> Farm -> User
 * - Filters by: Farm.owner == current user
 * - Applies to both collection (GET /api/journal_entries) and item (GET /api/journal_entries/{id})
 * 
 * Admin bypass:
 * - Users with ROLE_ADMIN can see all journal entries
 * 
 * @see JournalEntry
 */
final class JournalEntryQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    /**
     * Apply filtering to collection queries (GET /api/journal_entries).
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Apply filtering to item queries (GET /api/journal_entries/{id}).
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Add WHERE clause to filter journal entries by user ownership.
     * 
     * Only applies to JournalEntry queries and non-admin users.
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Only apply to JournalEntry queries
        if (JournalEntry::class !== $resourceClass) {
            return;
        }

        // Get current user
        $user = $this->security->getUser();
        
        // Skip filtering if no user is authenticated or user is admin
        if (!$user || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Get root alias (usually 'o' or 'journal_entry')
        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Join through: JournalEntry -> Reservoir -> Farm -> User
        $queryBuilder
            ->innerJoin(sprintf('%s.reservoir', $rootAlias), 'reservoir')
            ->innerJoin('reservoir.farm', 'farm')
            ->andWhere('farm.owner = :current_user')
            ->setParameter('current_user', $user);
    }
}
