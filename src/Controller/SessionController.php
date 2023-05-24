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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Controller\FormHelperTrait;
use Code202\Security\Entity\Session;
use Code202\Security\Form\Session\PagerType;
use Code202\Security\Request\Session\PagerRequest;
use Code202\Security\Service\Session\Deleter;
use Code202\Security\Service\Session\Informer;
use Code202\Security\Service\Session\Lister;
use Code202\Security\User\UserInterface;

#[AsController]
#[Route('/sessions', name: '.sessions')]
#[OA\Tag(name: 'Sessions')]
#[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
class SessionController
{
    use FormHelperTrait;

    #[Route('', name: '.list', methods: 'GET')]
    #[OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'maxPerPage', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'show', schema: new OA\Schema(type: 'string', enum: ['all', 'active', 'inactive']))]
    #[OA\QueryParameter(name: 'search', schema: new OA\Schema(type: 'string'))]
    #[OAA\PagerFantaResponse(new Model(type: Session::class, groups: ['list', 'session.info', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function list(
        FormFactoryInterface $factory,
        Request $request,
        UserInterface $user,
        Lister $lister,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(PagerType::class, new PagerRequest());

        $data = $this->handleRequest($form, $request);

        $pager = $lister->get([
            'page' => $data->page,
            'maxPerPage' => $data->maxPerPage,
            'show' => $data->show,
            'search' => $data->search,
            'account' => $user->getAccount(),
        ]);

        return new JsonResponse($serializer->serialize($pager, 'json', ['groups' => ['list', 'session.info', 'timestampable']]), 200, [], true);
    }

    #[Route('/summary', name: '.summary', methods: 'GET')]
    #[OA\Response(response: 200, description: 'Successful', content: new OA\JsonContent(ref :'#components/schemas/SessionSummaryResponse'))]
    public function summary(
        UserInterface $user,
        Informer $informer,
        SerializerInterface $serializer
    ): Response {
        $summary = $informer->getSummary($user->getAccount());

        return new JsonResponse($serializer->serialize($summary, 'json'), 200, [], true);
    }

    #[Route('/{uuid}', name: '.delete', methods: 'DELETE')]
    #[IsGranted('SECURITY.SESSION.DELETE', subject: 'session')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the session')]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Session::class, groups: ['list', 'session.info', 'timestampable']))]
    public function delete(
        Session $session,
        Deleter $deleter,
        SerializerInterface $serializer
    ): Response {
        $deleter->delete($session);

        return new JsonResponse($serializer->serialize($session, 'json', ['groups' => ['list', 'session.info', 'timestampable']]), 200, [], true);
    }
}
