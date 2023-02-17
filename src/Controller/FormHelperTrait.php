<?php

namespace Code202\Security\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait FormHelperTrait
{
    protected function handleRequest(FormInterface $form, Request $request): mixed
    {
        $method = $form->getConfig()->getMethod();

        if ($method !== $request->getMethod()) {
            throw new BadRequestHttpException('no submitted data');
        }

        if ('application/json' === $request->headers->get('Content-Type')) {
            $json = json_decode($request->getContent(), true);
            $form->submit($json);
        } elseif ('GET' === $method || 'HEAD' === $method || 'TRACE' === $method) {
            $form->submit($request->query->all(), false);
        } else {
            $form->submit($request->request->all(), false);
        }

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException('no submitted data');
        }

        if (!$form->isValid()) {
            throw new BadRequestHttpException($form->getErrors(true, false));
        }

        return $form->getData();
    }
}
