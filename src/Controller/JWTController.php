<?php

namespace Code202\Security\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsController]
#[Route('/jwt', name: '.jwt')]
#[OA\Tag(name: 'JWT')]
class JWTController
{
    #[Route('/refresh', name: '.refresh', methods: 'GET')]
    #[OA\Response(
        response: '200',
        description: 'Successful',
        content: new OA\JsonContent(
            ref: '#/components/schemas/LoginResponse'
        )
    )]
    public function refresh(
        UserInterface $user,
        JWTTokenManagerInterface $JWTManager
    ): Response {
        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }
}
