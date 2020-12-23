<?php

declare(strict_types=1);

namespace App\Pagination\Form;

use App\Form\Customer\StateChoiceType;
use App\Pagination\Criteria\CustomerCriteria;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Ttskch\PaginatorBundle\Form\CriteriaType;

class CustomerSearchType extends CriteriaType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerCriteria::class,
            'csrf_protection' => false,
        ]);
    }
}
