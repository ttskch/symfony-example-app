<?php

declare(strict_types=1);

namespace App\Form;

//use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'autofocus' => true,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
            ->add('news_letters', CheckboxType::class, [
                'required' => false,
                'label_attr' => [
                    'value' => 'Subscribe',
                    'class' => 'checkbox-inline',
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => $genderChoices = [
                    'Male' => 'Male',
                    'Female' => 'Female',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Please select', // just to add no-value option
                'attr' => [
                    'data-placeholder' => 'Please select',
                    'data-allow-clear' => 'true',
                    'class' => 'w-100',
                ],
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Choice(array_keys($genderChoices)),
                ],
            ])
            ->add('interesting_services', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Service A' => 'Service A',
                    'Service B' => 'Service B',
                    'Service C' => 'Service C',
                ],
                'expanded' => true,
                'multiple' => true,
                'attr' => [
                    'class' => 'form-check-inline',
                ],
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'rows' => 5,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('attachment', FileType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // uncomment if you want to bind to a class
            //'data_class' => Contact::class,
        ]);
    }
}
