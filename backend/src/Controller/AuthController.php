<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['message' => 'User not authenticated'], 401);
        }

        $userData = $this->serializer->serialize($user, 'json', [
            'groups' => ['user:read']
        ]);

        return new JsonResponse($userData, 200, [], true);
    }
}