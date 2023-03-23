<?php

namespace Code202\Security\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Code202\Security\Attribute\UuidOrMe;
use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Controller\FormHelperTrait;
use Code202\Security\Entity\Account;
use Code202\Security\Form\Account\PagerType;
use Code202\Security\Form\Account\UpdateNameType;
use Code202\Security\Request\Account\PagerRequest;
use Code202\Security\Request\Account\UpdateNameRequest;
use Code202\Security\Service\Account\Enabler;
use Code202\Security\Service\Account\Lister;
use Code202\Security\Service\Account\Updater;

#[AsController]
#[Route('/accounts', name: '.accounts')]
#[OA\Tag(name: 'Accounts')]
#[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
class AccountController
{
    use FormHelperTrait;

    #[Route('', name: '.list', methods: 'GET')]
    #[IsGranted('SECURITY.ACCOUNT.LIST')]
    #[OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'maxPerPage', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'show', schema: new OA\Schema(type: 'string', enum: ['all', 'active', 'inactive']))]
    #[OA\QueryParameter(name: 'sort', schema: new OA\Schema(type: 'string', enum: ['name', 'date']))]
    #[OAA\PagerFantaResponse(new Model(type: Account::class, groups: ['list']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function list(
        Request $request,
        FormFactoryInterface $factory,
        Lister $lister,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(PagerType::class, new PagerRequest());

        $data = $this->handleRequest($form, $request);

        $pager = $lister->get([
            'page' => $data->page,
            'maxPerPage' => $data->maxPerPage,
            'show' => $data->show,
            'sort' => $data->sort,
        ]);

        return new JsonResponse($serializer->serialize($pager, 'json', ['groups' => ['list']]), 200, [], true);
    }

    #[Route('/{uuid}', name: '.show', methods: 'GET')]
    #[IsGranted('SECURITY.ACCOUNT.SHOW', subject: 'account')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string'), description: 'Uuid of the account or "me"')]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    public function show(
        #[UuidOrMe] Account $account,
        SerializerInterface $serializer
    ): Response {
        return new JsonResponse($serializer->serialize($account, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/update-name', name: '.update-name', methods: 'PUT')]
    #[IsGranted('SECURITY.ACCOUNT.EDIT', subject: 'account')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string'), description: 'Uuid of the account or "me"')]
    #[OAA\PutBody(new Model(type: UpdateNameType::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    public function updateName(
        #[UuidOrMe] Account $account,
        Request $request,
        FormFactoryInterface $factory,
        Updater $updater,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(UpdateNameType::class, new UpdateNameRequest());

        $data = $this->handleRequest($form, $request);

        $updater->updateName($account, $data->name);

        return new JsonResponse($serializer->serialize($account, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/roles', name: '.roles', methods: 'GET')]
    #[IsGranted('SECURITY.ACCOUNT.ROLE', subject: 'account')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string'), description: 'Uuid of the account or "me"')]
    #[OA\Response(response: 200, description: 'Successful', content: new OA\JsonContent(ref :'#components/schemas/AccountRoleResponse'))]
    public function roles(
        #[UuidOrMe] Account $account,
        RoleHierarchyInterface $roleHierarchy,
        SerializerInterface $serializer
    ): Response {
        $roles = [];

        foreach ($account->getRoles() as $role) {
            $list = $roleHierarchy->getReachableRoleNames([$role]);

            unset($list[0]);
            $roles[] = [
                'role' => $role,
                'inherited' => array_values($list),
            ];
        }

        return new JsonResponse($serializer->serialize($roles, 'json', []), 200, [], true);
    }

    #[Route('/{uuid}/enable', name: '.enable', methods: 'PUT')]
    #[IsGranted('SECURITY.ACCOUNT.ENABLE', subject: 'account')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    public function enable(
        Account $account,
        Enabler $enabler,
        SerializerInterface $serializer
    ): Response {
        $enabler->enable($account);

        return new JsonResponse($serializer->serialize($account, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/disable', name: '.disable', methods: 'PUT')]
    #[IsGranted('SECURITY.ACCOUNT.DISABLE', subject: 'account')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Account::class, groups: ['list', 'timestampable']))]
    public function disable(
        Account $account,
        Enabler $enabler,
        SerializerInterface $serializer
    ): Response {
        $enabler->disable($account);

        return new JsonResponse($serializer->serialize($account, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }
}
