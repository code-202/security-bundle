<?php

namespace Code202\Security\Controller;

use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Entity\Authentication;
use Code202\Security\Exception\ExceptionInterface;
use Code202\Security\Request\Authentication\CreateEmailRequest;
use Code202\Security\Request\Authentication\PagerRequest;
use Code202\Security\Request\Authentication\UpdateEmailRequest;
use Code202\Security\Request\Authentication\UpdatePasswordRequest;
use Code202\Security\Request\Authentication\UpdateUsernameRequest;
use Code202\Security\Request\Authentication\VerifyTokenByEmailRequest;
use Code202\Security\Service\Authentication\Lister;
use Code202\Security\Service\Authentication\TokenByEmailCreator;
use Code202\Security\Service\Authentication\TokenByEmailRefresher;
use Code202\Security\Service\Authentication\TokenByEmailUpdater;
use Code202\Security\Service\Authentication\TokenByEmailVerifier;
use Code202\Security\Service\Authentication\UsernamePasswordUpdater;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
#[Route('/authentications', name: '.authentications')]
#[OA\Tag(name: 'Authentications')]
#[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
class AuthenticationController
{
    #[Route('', name: '.list', methods: 'GET')]
    #[OA\QueryParameter(name: 'account', schema: new OA\Schema(type: 'string'), required: true, description: 'Uuid of the account or "me"')]
    #[OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'maxPerPage', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'show', schema: new OA\Schema(type: 'string', enum: ['all', 'active', 'inactive']))]
    #[OAA\PagerFantaResponse(new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function list(
        #[MapQueryString] PagerRequest $request,
        Lister $lister,
        AuthorizationCheckerInterface $authorizationChecker,
        SerializerInterface $serializer
    ): Response {
        if (!$authorizationChecker->isGranted('SECURITY.ACCOUNT.AUTHENTICATIONS', $request->account)) {
            throw new AccessDeniedException('Access of authentication of this account is denied !');
        }

        $pager = $lister->get([
            'page' => $request->page,
            'maxPerPage' => $request->maxPerPage,
            'show' => $request->show,
            'account' => $request->account,
        ]);

        return new JsonResponse($serializer->serialize($pager, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}/update-password', name: '.update-password', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[IsGranted('SECURITY.SESSION.TRUSTED')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: UpdatePasswordRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function updatePassword(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Authentication $authentication,
        #[MapRequestPayload] UpdatePasswordRequest $request,
        UsernamePasswordUpdater $updater,
        SerializerInterface $serializer
    ): Response {
        try {
            $updater->updatePassword($authentication, $request->new);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}/update-username', name: '.update-username', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[IsGranted('SECURITY.SESSION.TRUSTED')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: UpdateUsernameRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function updateUsername(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Authentication $authentication,
        #[MapRequestPayload] UpdateUsernameRequest $request,
        UsernamePasswordUpdater $updater,
        SerializerInterface $serializer
    ): Response {
        try {
            $updater->updateUsername($authentication, $request->username);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/create-email', name: '.create-email', methods: 'POST')]
    #[IsGranted('SECURITY.SESSION.TRUSTED')]
    #[OAA\PostBody(new Model(type: CreateEmailRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function createEmail(
        #[MapRequestPayload] CreateEmailRequest $request,
        TokenByEmailCreator $creator,
        UserInterface $user,
        SerializerInterface $serializer
    ): Response {
        try {
            $authentication = $creator->createEmail($user->getAccount(), $request->email);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}/send-token-by-email', name: '.send-token-by-email', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OA\Response(response: 204, description: 'Successful', content: new OA\MediaType(mediaType: 'application/json'))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function sendTokenByEmail(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Authentication $authentication,
        TokenByEmailRefresher $refresher
    ): Response {
        try {
            $refresher->refresh($authentication);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{uuid}/verify-token-by-email', name: '.verify-token-by-email', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: VerifyTokenByEmailRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function verifyTokenByEmail(
        #[MapRequestPayload] VerifyTokenByEmailRequest $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Authentication $authentication,
        TokenByEmailVerifier $verifier,
        SerializerInterface $serializer
    ): Response {
        try {
            $verifier->verify($authentication, $request->token);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }

    #[Route('/{uuid}/update-email', name: '.update-email', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[IsGranted('SECURITY.SESSION.TRUSTED')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: UpdateEmailRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function updateEmail(
        #[MapRequestPayload] UpdateEmailRequest $request,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Authentication $authentication,
        TokenByEmailUpdater $updater,
        SerializerInterface $serializer
    ): Response {
        try {
            $updater->updateEmail($authentication, $request->email);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), Response::HTTP_OK, [], true);
    }
}
