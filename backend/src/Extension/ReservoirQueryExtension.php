<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Reservoir;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Filter Reservoir collections to show only reservoirs from farms owned by the current user.
 * 
 * This extension automatically adds a WHERE clause to filter reservoirs
 * based on the farm owner being the current authenticated user.
 */
final class ReservoirQueryExtension implements QueryCollectionExtensionInterface
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
        if ($resourceClass !== Reservoir::class) {
            return;
        }

        $user = $this->security->getUser();
        
        if (!$user) {
            return;
        }

        // Admins can see all reservoirs
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        
        // Join with farm to filter by owner
        $queryBuilder
            ->innerJoin(sprintf('%s.farm', $rootAlias), 'farm')
            ->andWhere('farm.owner = :current_user')
            ->setParameter('current_user', $user);
    }
}
