<?php

declare(strict_types=1);

namespace Code202\Security\Form\Authentication;

use Code202\Security\Request\Authentication\VerifyTokenByEmailRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VerifyTokenByEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('put')
            ->add('token')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VerifyTokenByEmailRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
