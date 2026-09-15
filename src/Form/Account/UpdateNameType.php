<?php

declare(strict_types=1);

namespace Code202\Security\Form\Account;

use Code202\Security\Request\Account\UpdateNameRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<UpdateNameRequest>
 */
class UpdateNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('put')
            ->add('name')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateNameRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
