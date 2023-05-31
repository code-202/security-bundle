<?php

namespace Code202\Security\Form\Session;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Request\Session\TrustPasswordRequest;

class TrustPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('put')
            ->add('password')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrustPasswordRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
