<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', EntityType::class, [
                'class' => Address::class,
                'choice_label' => 'line',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'New' => OrderStatus::NEW,
                    'Processing' => OrderStatus::PROCESSING,
                    'Cancelled' => OrderStatus::CANCELLED,
                    'Completed' => OrderStatus::COMPLETED,
                ],
                'choice_label' => function ($choice, $key, $value) {
                    return ucfirst($key);
                },
                'choice_value' => function (?OrderStatus $status) {
                    return $status ? $status->value : null;
                },
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
