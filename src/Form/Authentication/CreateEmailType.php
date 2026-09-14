<?php

declare(strict_types=1);

namespace Code202\Security\Form\Authentication;

use Code202\Security\Request\Authentication\CreateEmailRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('post')
            ->add('email')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateEmailRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
