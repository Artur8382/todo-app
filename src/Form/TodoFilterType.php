<?php

namespace App\Form;

use App\Enum\TodoStatus;
use App\Filter\TodoFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TodoFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'Search by title...'],
            ])
            ->add('status', EnumType::class, [
                'class' => TodoStatus::class,
                'required' => false,
                'placeholder' => 'All statuses',
            ])
            ->add('sort', ChoiceType::class, [
                'placeholder' => false,
                'choices' => [
                    'Newest first' => 'DESC',
                    'Oldest first' => 'ASC',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TodoFilter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}