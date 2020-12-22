<?php

declare(strict_types=1);

namespace App\Form\Project;

use App\EntityConstant\ProjectConstant;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class StateChoiceType extends ChoiceType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator, ChoiceListFactoryInterface $choiceListFactory = null)
    {
        parent::__construct($choiceListFactory);

        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => array_combine(ProjectConstant::getValidStates(), ProjectConstant::getValidStates()),
            'multiple' => false,
            'placeholder' => '',
            'attr' => [
                'data-placeholder' => $this->translator->trans('Please select'),
                'data-allow-clear' => true,
                'class' => 'w-100',
            ],
        ]);
    }
}
