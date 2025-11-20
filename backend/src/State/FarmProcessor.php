<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Farm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Processor that automatically assigns the current user as the owner of a farm.
 * 
 * This ensures that when a user creates a farm, they are automatically set as the owner,
 * even if they don't specify it in the request.
 */
final class FarmProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Farm) {
            // Auto-assign the current user as owner when creating a farm
            if ($operation instanceof \ApiPlatform\Metadata\Post && !$data->getOwner()) {
                $data->setOwner($this->security->getUser());
            }

            // Persist the entity
            $this->entityManager->persist($data);
            $this->entityManager->flush();

            return $data;
        }

        return $data;
    }
}
