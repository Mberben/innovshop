<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Étape 2 de la commande : adresse de livraison.
 * Les règles de validation sont sur l'entité Order (attributs Assert).
 */
class ShippingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingName', TextType::class, ['label' => 'Nom du destinataire'])
            ->add('shippingAddress', TextType::class, ['label' => 'Adresse'])
            ->add('shippingPostalCode', TextType::class, ['label' => 'Code postal'])
            ->add('shippingCity', TextType::class, ['label' => 'Ville'])
            ->add('shippingPhone', TelType::class, ['label' => 'Téléphone'])
            ->add('saveAddress', CheckboxType::class, [
                'label' => 'Enregistrer cette adresse dans mon profil',
                'mapped' => false,
                'required' => false,
                'data' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Order::class]);
    }
}
