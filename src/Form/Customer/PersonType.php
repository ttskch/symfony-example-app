<?php

declare(strict_types=1);

namespace App\Form\Customer;

use App\Entity\Customer\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full name',
                'attr' => [
                    'required' => true,
                ],
                'label_attr' => [
                    'class' => 'required',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Email',
            ])
            ->add('tel', TelType::class, [
                'required' => false,
                'label' => 'Tel',
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
            'data_class' => Person::class,
        ]);
    }
}
