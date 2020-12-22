<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Project;
use App\Entity\User;
use App\Form\Project\StateChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'label' => 'Customer',
                'placeholder' => '',
                'attr' => [
                    'autofocus' => true,
                    'data-placeholder' => $this->translator->trans('Please select'),
                    'data-allow-clear' => true,
                    'class' => 'w-100',
                ],
            ])
            ->add('name', TextType::class, [
                'label' => 'Project name',
            ])
            ->add('state', StateChoiceType::class, [
                'label' => 'State',
            ])
            ->add('user', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'label' => 'Assignee',
                'placeholder' => '',
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Please select'),
                    'data-allow-clear' => true,
                    'class' => 'w-100',
                ],
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'label' => 'Note',
                'attr' => [
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
