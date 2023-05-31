<?php

namespace Code202\Security\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Code202\Security\Bridge\OpenApi\Attributes as OAA;
use Code202\Security\Controller\FormHelperTrait;
use Code202\Security\Entity\Authentication;
use Code202\Security\Exception\ExceptionInterface;
use Code202\Security\Form\Authentication\CreateEmailType;
use Code202\Security\Form\Authentication\PagerType;
use Code202\Security\Form\Authentication\UpdateEmailType;
use Code202\Security\Form\Authentication\UpdatePasswordType;
use Code202\Security\Form\Authentication\UpdateUsernameType;
use Code202\Security\Form\Authentication\VerifyTokenByEmailType;
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

#[AsController]
#[Route('/authentications', name: '.authentications')]
#[OA\Tag(name: 'Authentications')]
#[OA\Response(response: 401, ref: '#/components/responses/401-Unauthorized')]
class AuthenticationController
{
    use FormHelperTrait;

    #[Route('', name: '.list', methods: 'GET')]
    #[OA\QueryParameter(name: 'account', schema: new OA\Schema(type: 'string'), required: true, description: 'Uuid of the account or "me"')]
    #[OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'maxPerPage', schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(name: 'show', schema: new OA\Schema(type: 'string', enum: ['all', 'active', 'inactive']))]
    #[OAA\PagerFantaResponse(new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function list(
        Request $request,
        FormFactoryInterface $factory,
        Lister $lister,
        AuthorizationCheckerInterface $authorizationChecker,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(PagerType::class, new PagerRequest());

        $data = $this->handleRequest($form, $request);

        if (!$authorizationChecker->isGranted('SECURITY.ACCOUNT.AUTHENTICATIONS', $data->account)) {
            throw new AccessDeniedException('Access of authentication of this account is denied !');
        }

        $pager = $lister->get([
            'page' => $data->page,
            'maxPerPage' => $data->maxPerPage,
            'show' => $data->show,
            'account' => $data->account,
        ]);

        return new JsonResponse($serializer->serialize($pager, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/update-password', name: '.update-password', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: UpdatePasswordRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function updatePassword(
        Authentication $authentication,
        Request $request,
        FormFactoryInterface $factory,
        UsernamePasswordUpdater $updater,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(UpdatePasswordType::class, new UpdatePasswordRequest());

        $data = $this->handleRequest($form, $request);

        try {
            $updater->updatePassword($authentication, $data->new);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/update-username', name: '.update-username', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: UpdateUsernameRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function updateUsername(
        Authentication $authentication,
        Request $request,
        FormFactoryInterface $factory,
        UsernamePasswordUpdater $updater,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(UpdateUsernameType::class, new UpdateUsernameRequest());

        $data = $this->handleRequest($form, $request);

        try {
            $updater->updateUsername($authentication, $data->username);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/create-email', name: '.create-email', methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OAA\PostBody(new Model(type: CreateEmailRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function createEmail(
        Request $request,
        FormFactoryInterface $factory,
        TokenByEmailCreator $creator,
        UserInterface $user,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(CreateEmailType::class, new CreateEmailRequest());

        $data = $this->handleRequest($form, $request);

        try {
            $authentication = $creator->createEmail($user->getAccount(), $data->email);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/send-token-by-email', name: '.send-token-by-email', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OA\Response(response: 204, description: 'Successful', content: new OA\MediaType(mediaType: 'application/json'))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function sendTokenByEmail(
        Authentication $authentication,
        TokenByEmailRefresher $refresher
    ): Response {
        try {
            $refresher->refresh($authentication);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new Response(null, 204);
    }

    #[Route('/{uuid}/verify-token-by-email', name: '.verify-token-by-email', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: VerifyTokenByEmailRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function verifyTokenByEmail(
        Request $request,
        FormFactoryInterface $factory,
        Authentication $authentication,
        TokenByEmailVerifier $verifier,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(VerifyTokenByEmailType::class, new VerifyTokenByEmailRequest());

        $data = $this->handleRequest($form, $request);

        try {
            $verifier->verify($authentication, $data->token);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }

    #[Route('/{uuid}/update-email', name: '.update-email', methods: 'PUT')]
    #[IsGranted('SECURITY.AUTHENTICATION.EDIT', subject: 'authentication')]
    #[OA\PathParameter(name: 'uuid', schema: new OA\Schema(type: 'string', format: 'uuid'), description: 'Uuid of the authentication')]
    #[OAA\PutBody(new Model(type: UpdateEmailRequest::class))]
    #[OA\Response(response: 200, description: 'Successful', content: new Model(type: Authentication::class, groups: ['list', 'timestampable']))]
    #[OA\Response(response: 400, ref: '#/components/responses/400-BadRequest')]
    public function updateEmail(
        Request $request,
        FormFactoryInterface $factory,
        Authentication $authentication,
        TokenByEmailUpdater $updater,
        SerializerInterface $serializer
    ): Response {
        $form = $factory->create(UpdateEmailType::class, new UpdateEmailRequest());

        $data = $this->handleRequest($form, $request);

        try {
            $updater->updateEmail($authentication, $data->email);
        } catch (ExceptionInterface $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse($serializer->serialize($authentication, 'json', ['groups' => ['list', 'timestampable']]), 200, [], true);
    }
}
