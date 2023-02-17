<?php

namespace Code202\Security\Form\Authentication;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Form\DataTransformer\UuidToAccountTransformer;
use Code202\Security\Form\PagerType as BasePagerType;
use Code202\Security\Request\Authentication\PagerRequest;

class PagerType extends BasePagerType
{
    private UuidToAccountTransformer $transformer;

    public function __construct(
        UuidToAccountTransformer $transformer
    ) {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('show')
            ->add('account')
        ;

        $builder->get('account')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PagerRequest::class,
        ]);
    }
}
