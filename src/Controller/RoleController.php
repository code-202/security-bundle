<?php

namespace Code202\Security\Controller;

use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Entity\Account;
use Code202\Security\Request\Role\GrantRequest;
use Code202\Security\Request\Role\RevokeRequest;
use Code202\Security\Service\Account\RoleManipulator;
use Code202\Security\Service\RoleStrategy\Manager as RoleManager;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[Route('/roles', name: '.roles')]
#[OA\Tag(name: 'Roles')]
class RoleController
{
    #[Route('/manipulatable', name: '.manipulatable', methods: 'GET')]
    #[OA\Response(response: 200, description: 'Successful', content: new OA\JsonContent(ref : '#components/schemas/RoleManipulateResponse'))]
    public function grantable(
        RoleManager $manager,
        SerializerInterface $serializer
    ): Response {
        return new JsonResponse($serializer->serialize([
            'grantables' => $manager->getGrantableRoles(),
            'revocables' => $manager->getRevocableRoles(),
        ], 'json', []), Response::HTTP_OK, [], true);
    }

    #[Route('/grant', name: '.grant', methods: 'PUT')]
    #[OAA\PutBody(new Model(type: GrantRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
    public function grant(
        #[MapRequestPayload] GrantRequest $request,
        AuthorizationCheckerInterface $authorizationChecker,
        RoleManipulator $manipulator,
        SerializerInterface $serializer
    ): Response {
        if (!$authorizationChecker->isGranted('SECURITY.ROLE.GRANT', $request->role)) {
            throw new AccessDeniedException('You are not allowed to grant the role : ' . $request->role);
        }

        $manipulator->grant($request->account, $request->role);

        return new JsonResponse($serializer->serialize($request->account, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/revoke', name: '.revoke', methods: 'PUT')]
    #[OAA\PutBody(new Model(type: RevokeRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
    public function revoke(
        #[MapRequestPayload] RevokeRequest $request,
        AuthorizationCheckerInterface $authorizationChecker,
        RoleManipulator $manipulator,
        SerializerInterface $serializer
    ): Response {
        if (!$authorizationChecker->isGranted('SECURITY.ROLE.REVOKE', $request->role)) {
            throw new AccessDeniedException('You are not allowed to revoke the role : ' . $request->role);
        }

        $manipulator->revoke($request->account, $request->role);

        return new JsonResponse($serializer->serialize($request->account, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }
}
