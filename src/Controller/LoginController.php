<?php

namespace Code202\Security\Controller;

use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: ' Login')]
#[Security(name: null)]
class LoginController
{
    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/x-www-form-urlencoded',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginUsernameRequest'),
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginUsernameRequest'),
                ),
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginUsernameRequest'),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            ),
        ]
    )]
    public function username(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND, []);
    }

    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginUsernameRequest'),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            ),
        ]
    )]
    public function usernameJson(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND, []);
    }

    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/x-www-form-urlencoded',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginUsernameRequest'),
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginUsernameRequest'),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            ),
        ]
    )]
    public function usernameForm(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND, []);
    }

    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/x-www-form-urlencoded',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginEmailRequest'),
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginEmailRequest'),
                ),
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginEmailRequest'),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            ),
        ]
    )]
    public function email(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND, []);
    }

    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginEmailRequest'),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            ),
        ]
    )]
    public function emailJson(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND, []);
    }

    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: [
                new OA\MediaType(
                    mediaType: 'application/x-www-form-urlencoded',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginEmailRequest'),
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/LoginEmailRequest'),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Successful',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/LoginResponse'
                )
            ),
        ]
    )]
    public function emailForm(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND, []);
    }
}
