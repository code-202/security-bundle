<?php

namespace Code202\Security\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Controller\FormHelperTrait;
use Code202\Security\Entity\Account;
use Code202\Security\Form\Role\GrantType;
use Code202\Security\Form\Role\RevokeType;
use Code202\Security\Request\Role\GrantRequest;
use Code202\Security\Request\Role\RevokeRequest;
use Code202\Security\Service\Account\RoleManipulator;
use Code202\Security\Service\RoleStrategy\Manager as RoleManager;

#[AsController]
#[Route('/roles', name: '.roles')]
#[OA\Tag(name: 'Roles')]
class RoleController
{
    use FormHelperTrait;

    #[Route('/manipulatable', name: '.manipulatable', methods: 'GET')]
    #[OA\Response(response: 200, description: 'Successful', content: new OA\JsonContent(ref :'#components/schemas/RoleManipulateResponse'))]
    public function grantable(
        RoleManager $manager,
        SerializerInterface $serializer
    ): Response {
        return new JsonResponse($serializer->serialize([
            'grantables' => $manager->getGrantableRoles(),
            'revocables' => $manager->getRevocableRoles(),
        ], 'json', []), 200, [], true);
    }

    #[Route('/grant', name: '.grant', methods: 'PUT')]
    #[OAA\PutBody(new Model(type: GrantRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
    public function grant(
        Request $request,
        FormFactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        RoleManipulator $manipulator,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(GrantType::class, new GrantRequest());

        $data = $this->handleRequest($form, $request);

        if (!$authorizationChecker->isGranted('SECURITY.ROLE.GRANT', $data->role)) {
            throw new AccessDeniedException('You are not allowed to grant the role : '.$data->role);
        }

        $manipulator->grant($data->account, $data->role);

        return new JsonResponse($serializer->serialize($data->account, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/revoke', name: '.revoke', methods: 'PUT')]
    #[OAA\PutBody(new Model(type: RevokeRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
    public function revoke(
        Request $request,
        FormFactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        RoleManipulator $manipulator,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(RevokeType::class, new RevokeRequest());

        $data = $this->handleRequest($form, $request);

        if (!$authorizationChecker->isGranted('SECURITY.ROLE.REVOKE', $data->role)) {
            throw new AccessDeniedException('You are not allowed to revoke the role : '.$data->role);
        }

        $manipulator->revoke($data->account, $data->role);

        return new JsonResponse($serializer->serialize($data->account, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }
}
