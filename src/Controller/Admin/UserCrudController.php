<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Client')
            ->setEntityLabelInPlural('Clients')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['email', 'firstName', 'lastName', 'city']);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Les comptes se créent via l'inscription (mot de passe choisi par le client)
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Compte');
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield EmailField::new('email', 'Email');
        // Le rôle ROLE_USER est toujours ajouté automatiquement par User::getRoles()
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices(['Administrateur' => 'ROLE_ADMIN'])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges(['ROLE_ADMIN' => 'danger'])
            ->setHelp('Cocher pour donner l\'accès au back-office.');
        yield DateTimeField::new('createdAt', 'Inscrit le')->hideOnForm();

        yield FormField::addFieldset('Coordonnées')->hideOnIndex();
        yield TelephoneField::new('phone', 'Téléphone')->hideOnIndex();
        yield TextField::new('address', 'Adresse')->hideOnIndex();
        yield TextField::new('postalCode', 'Code postal')->hideOnIndex();
        yield TextField::new('city', 'Ville');

        // Historique des commandes du client (page détail)
        yield FormField::addFieldset('Historique des commandes')->onlyOnDetail();
        yield CollectionField::new('orders', 'Commandes')->onlyOnDetail();
    }
}
