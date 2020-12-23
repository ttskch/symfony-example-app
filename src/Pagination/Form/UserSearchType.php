<?php

declare(strict_types=1);

namespace App\Pagination\Form;

use App\Pagination\Criteria\UserCriteria;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Ttskch\PaginatorBundle\Form\CriteriaType;

class UserSearchType extends CriteriaType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserCriteria::class,
            'csrf_protection' => false,
        ]);
    }
}
