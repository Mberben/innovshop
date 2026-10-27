<?php

namespace App\Controller\Admin;

use App\Entity\ProductOption;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Utilisé uniquement à l'intérieur du formulaire Produit (pas de lien dans le menu).
 */
class ProductOptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductOption::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('type', 'Type')->setHelp('Ex. : Couleur, Taille, Stockage');
        yield TextField::new('value', 'Valeur')->setHelp('Ex. : Noir, XL, 256 Go');
    }
}

