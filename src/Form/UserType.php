<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Security\RoleManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserType extends AbstractType
{
    private RoleManager $rm;
    private TranslatorInterface $translator;

    public function __construct(RoleManager $rm, TranslatorInterface $translator)
    {
        $this->rm = $rm;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // exclude ROLE_ALLOWED_TO_VIEW from choices
        $roles = array_filter($this->rm->getReachableRoles(), fn(string $role) => $role !== 'ROLE_ALLOWED_TO_VIEW');

        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('roles', ChoiceType::class, [
                'required' => false,
                'choices' => array_combine($roles, $roles),
                'multiple' => true,
                'placeholder' => '',
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Please select'),
                    'data-allow-clear' => true,
                    'class' => 'w-100',
                ],
            ])
            ->add('displayName', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => User::class,
                'validation_groups' => ['registration'],
            ])
        ;
    }
}
