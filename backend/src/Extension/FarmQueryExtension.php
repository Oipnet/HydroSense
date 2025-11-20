<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Farm;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Filter Farm collections to show only farms owned by the current user.
 * 
 * This extension automatically adds a WHERE clause to filter farms
 * based on the current authenticated user.
 */
final class FarmQueryExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Farm::class) {
            return;
        }

        $user = $this->security->getUser();
        
        if (!$user) {
            return;
        }

        // Admins can see all farms
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.owner = :current_user', $rootAlias))
            ->setParameter('current_user', $user);
    }
}
