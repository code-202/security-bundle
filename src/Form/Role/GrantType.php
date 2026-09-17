<?php

declare(strict_types=1);

namespace Code202\Security\Form\Role;

use Code202\Security\Form\DataTransformer\UuidToAccountTransformer;
use Code202\Security\Request\Role\GrantRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<GrantRequest> */
class GrantType extends AbstractType
{
    public function __construct(
        private readonly UuidToAccountTransformer $transformer
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('put')
            ->add('role')
            ->add('account')
        ;

        $builder->get('account')
            ->addModelTransformer($this->transformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GrantRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
