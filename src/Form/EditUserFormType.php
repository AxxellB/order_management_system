<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Address;
use App\Form\AddressFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('selectAddress', EntityType::class, [
                'class' => Address::class,
                'choices' => $options['addresses'],
                'choice_label' => 'fullAddress',
                'placeholder' => 'Choose an address',
                'mapped' => false,
                'required' => true,
            ])
            ->add('addressDetails', AddressFormType::class, [
                'label' => 'Edit Selected Address',
                'mapped' => false,
                'disabled' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'addresses' => [],
        ]);
    }
}

