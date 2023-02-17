<?php

namespace Code202\Security\Form\Session;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Form\PagerType as BasePagerType;
use Code202\Security\Request\Session\PagerRequest;

class PagerType extends BasePagerType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('show')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PagerRequest::class,
        ]);
    }
}
