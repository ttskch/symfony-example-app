<?php

declare(strict_types=1);

namespace App\Pagination\Form;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\Project\StateChoiceType;
use App\Pagination\Criteria\ProjectCriteria;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Ttskch\PaginatorBundle\Form\CriteriaType;

class ProjectSearchType extends CriteriaType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('query', SearchType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('Full text search'),
                    'class' => 'w-100',
                ],
            ])
            ->add('states', StateChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Select states'),
                    'data-allow-clear' => true,
                    'class' => 'w-100',
                ],
            ])
            ->add('customers', EntityType::class, [
                'required' => false,
                'class' => Customer::class,
                'multiple' => true,
                'placeholder' => '',
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Select customers'),
                    'data-allow-clear' => true,
                    'class' => 'w-100',
                ],
            ])
            ->add('users', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'multiple' => true,
                'placeholder' => '',
                'attr' => [
                    'data-placeholder' => $this->translator->trans('Select assignees'),
                    'data-allow-clear' => true,
                    'data-width' => 'element',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectCriteria::class,
            'csrf_protection' => false,
        ]);
    }
}
