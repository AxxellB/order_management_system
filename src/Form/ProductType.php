<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name.'
                    ])
                ]
            ])
            ->add('price', MoneyType::class, [
                'scale' => 2,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the price.'
                    ]),
                ],
                'attr' => [
                    'step' => '0.01',
                ]
            ])
            ->add('description')
            ->add('stockQuantity')

            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label'=> 'Categories',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Count([
                        'min' => 1,
                        'minMessage' => 'Please select at least one category.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
