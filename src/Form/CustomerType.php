<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Customer;
use App\Form\Customer\PersonType;
use App\Form\Customer\StateChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Customer name',
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('state', StateChoiceType::class, [
                'label' => 'State',
            ])
            ->add('people', CollectionType::class, [
                'required' => false,
                'entry_type' => PersonType::class,
                'label' => 'Contact people',
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
