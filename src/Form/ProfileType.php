<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('phone', TelType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('address', TextType::class, ['label' => 'Adresse', 'required' => false])
            ->add('postalCode', TextType::class, ['label' => 'Code postal', 'required' => false])
            ->add('city', TextType::class, ['label' => 'Ville', 'required' => false])
            // Stocké en centimes : divisor 100 convertit automatiquement 500 € <-> 50000
            ->add('monthlySpendingGoal', MoneyType::class, [
                'label' => 'Objectif de dépenses mensuel',
                'currency' => 'EUR',
                'divisor' => 100,
                'required' => false,
                'help' => 'Laissez vide si vous ne souhaitez pas suivre vos dépenses.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
