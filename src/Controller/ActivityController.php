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
use Symfony\Component\Serializer\SerializerInterface;
use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Controller\FormHelperTrait;
use Code202\Security\Entity\Activity\Activity;
use Code202\Security\Form\PagerType;
use Code202\Security\Request\PagerRequest;
use Code202\Security\Service\Activity\Lister;
use Code202\Security\Service\Activity\Target\Provider as TargetProvider;
use Code202\Security\User\UserInterface;

#[AsController]
#[Route('/activities', name: '.activities')]
#[OA\Tag(name: 'Activities')]
#[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
class ActivityController
{
    use FormHelperTrait;

    #[Route('', name: '.list', methods: 'GET')]
    #[OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'maxPerPage', schema: new OA\Schema(type: 'integer'))]
    #[OAA\PagerFantaResponse(new Model(type: Activity::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function list(
        Request $request,
        FormFactoryInterface $factory,
        UserInterface $user,
        Lister $lister,
        TargetProvider $targetProvider,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(PagerType::class, new PagerRequest());

        $data = $this->handleRequest($form, $request);

        $targets = $targetProvider->findAll($user->getAccount());

        $pager = $lister->get([
            'page' => $data->page,
            'maxPerPage' => $data->maxPerPage,
            'targets' => $targets,
        ]);

        return new JsonResponse($serializer->serialize($pager, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }
}
