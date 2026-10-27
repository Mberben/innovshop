<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['reference', 'shippingName', 'user.email']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Les commandes sont créées par les clients sur le site :
        // l'admin peut les consulter et changer leur statut, pas en créer ni en supprimer.
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status')
            ->add('createdAt')
            ->add('user');
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Commande');
        yield TextField::new('reference', 'Référence')->setDisabled();
        yield AssociationField::new('user', 'Client')->setDisabled();
        yield DateTimeField::new('createdAt', 'Date')->setDisabled();
        yield MoneyField::new('total', 'Total')->setCurrency('EUR')->setDisabled();
        // Seul champ modifiable : le statut (badges de couleur dans la liste)
        yield ChoiceField::new('status', 'Statut')
            ->renderAsBadges([
                OrderStatus::PENDING->name => 'warning',
                OrderStatus::VALIDATED->name => 'info',
                OrderStatus::SHIPPED->name => 'success',
                OrderStatus::CANCELLED->name => 'danger',
            ]);

        yield FormField::addFieldset('Livraison')->hideOnIndex();
        yield TextField::new('shippingName', 'Destinataire')->hideOnIndex()->setDisabled();
        yield TextField::new('shippingAddress', 'Adresse')->hideOnIndex()->setDisabled();
        yield TextField::new('shippingPostalCode', 'Code postal')->hideOnIndex()->setDisabled();
        yield TextField::new('shippingCity', 'Ville')->hideOnIndex()->setDisabled();
        yield TextField::new('shippingPhone', 'Téléphone')->hideOnIndex()->setDisabled();

        yield FormField::addFieldset('Articles')->onlyOnDetail();
        yield CollectionField::new('orderLines', 'Articles')->onlyOnDetail();
    }
}
