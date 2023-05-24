<?php

namespace Code202\Security\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: ' Logout')]
#[Route('/logout', name: '.logout')]
class LogoutController
{
    #[OA\Post(
        responses: [
            new OA\Response(
                response: '204',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            )
        ]
    )]
    #[Route('', name: '', methods: 'POST')]
    public function logout(Security $security): Response
    {
        $security->logout();

        return new Response(null, 204, [], true);
    }
}
