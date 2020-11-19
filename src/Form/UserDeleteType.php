<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserDeleteType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $self = $options['self'];

        $builder
            ->add('alternateUser', EntityType::class, [
                'class' => User::class,
                'placeholder' => '',
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Please select'),
                    'data-allow-clear' => true,
                    'class' => 'w-100',
                ],
                'query_builder' => function(UserRepository $repository) use ($self) {
                    return $repository->createQueryBuilder('u')
                        ->where('u != :self')
                        ->setParameter('self', $self)
                    ;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'method' => 'DELETE',
            ])
            ->setRequired('self')
            ->setAllowedTypes('self', [User::class])
        ;
    }
}
