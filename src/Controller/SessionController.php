<?php

namespace Code202\Security\Controller;

use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Entity\Session;
use Code202\Security\Exception\ExceptionInterface;
use Code202\Security\Request\Session\PagerRequest;
use Code202\Security\Request\Session\TrustPasswordRequest;
use Code202\Security\Service\Session\Deleter;
use Code202\Security\Service\Session\Informer;
use Code202\Security\Service\Session\Lister;
use Code202\Security\Service\Session\PasswordTruster;
use Code202\Security\Service\Session\Truster;
use Code202\Security\User\UserInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[Route('/sessions', name: '.sessions')]
#[OA\Tag(name: 'Sessions')]
#[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
class SessionController
{
    #[Route('', name: '.list', methods: 'GET')]
    #[OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'maxPerPage', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'show', schema: new OA\Schema(type: 'string', enum: ['all', 'active', 'inactive']))]
    #[OA\QueryParameter(name: 'search', schema: new OA\Schema(type: 'string'))]
    #[OAA\PagerFantaResponse(new Model(type: Session::class, groups: ['list', 'session.info', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function list(
        #[MapQueryString] PagerRequest $request,
        UserInterface $user,
        Lister $lister,
        SerializerInterface $serializer
    ): Response {
        $pager = $lister->get([
            'page' => $request->page,
            'maxPerPage' => $request->maxPerPage,
            'show' => $request->show,
            'search' => $request->search,
            'account' => $user->getAccount(),
        ]);

        return new JsonResponse($serializer->serialize($pager, 'json', ['groups' => ['list', 'session.info', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/summary', name: '.summary', methods: 'GET')]
    #[OA\Response(response: 200, description: 'Successful', content: new OA\JsonContent(ref : '#components/schemas/SessionSummaryResponse'))]
    public function summary(
        UserInterface $user,
        Informer $informer,
        SerializerInterface $serializer
    ): Response {
        $summary = $informer->getSummary($user->getAccount());

        return new JsonResponse($serializer->serialize($summary, 'json'), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}/trust', name: '.trust', methods: 'PUT')]
    #[IsGranted('SECURITY.SESSION.TRUST', subject: 'session')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the session')]
    #[OAA\PutBody(new Model(type: TrustPasswordRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Session::class, groups: ['list', 'session.info', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function trust(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Session $session,
        #[MapRequestPayload] TrustPasswordRequest $request,
        PasswordTruster $truster,
        SerializerInterface $serializer
    ): Response {
        try {
            $truster->trust($session, $request->password);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($session, 'json', ['groups' => ['list', 'session.info', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}/untrust', name: '.untrust', methods: 'PUT')]
    #[IsGranted('SECURITY.SESSION.UNTRUST', subject: 'session')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the session')]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Session::class, groups: ['list', 'session.info', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function untrust(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Session $session,
        Truster $truster,
        SerializerInterface $serializer
    ): Response {
        $truster->untrust($session);

        return new JsonResponse($serializer->serialize($session, 'json', ['groups' => ['list', 'session.info', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}', name: '.delete', methods: 'DELETE')]
    #[IsGranted('SECURITY.SESSION.DELETE', subject: 'session')]
    #[IsGranted('SECURITY.SESSION.TRUSTED')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the session')]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Session::class, groups: ['list', 'session.info', 'timestampable']))]
    public function delete(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Session $session,
        Deleter $deleter,
        SerializerInterface $serializer
    ): Response {
        $deleter->delete($session);

        return new JsonResponse($serializer->serialize($session, 'json', ['groups' => ['list', 'session.info', 'timestampable']]), Response::HTTP_OK, [], true);
    }
}
