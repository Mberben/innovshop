<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ImageField::new('mainImage', 'Image')
            ->setBasePath('uploads/products')
            ->onlyOnIndex();
        yield TextField::new('name', 'Nom');
        yield SlugField::new('slug')
            ->setTargetFieldName('name')
            ->hideOnIndex();
        yield AssociationField::new('category', 'Catégorie');
        // Le prix est stocké en centimes : MoneyField fait la conversion tout seul
        yield MoneyField::new('price', 'Prix')->setCurrency('EUR');
        yield BooleanField::new('featured', 'À la une')
            // pas d'interrupteur dans la liste : la limite de 3 est vérifiée dans le formulaire
            ->renderAsSwitch(false)
            ->setHelp('3 produits maximum à la une (affichés sur la page d\'accueil).');
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield TextareaField::new('specifications', 'Spécifications')->hideOnIndex();

        // Les options s'ajoutent directement dans le formulaire du produit.
        // Les images ont leur propre écran (menu Catalogue > Images) : l'upload
        // de fichiers dans un sous-formulaire n'est pas fiable avec EasyAdmin.
        yield CollectionField::new('options', 'Options (couleur, taille…)')
            ->useEntryCrudForm(ProductOptionCrudController::class)
            ->onlyOnForms();

        yield DateTimeField::new('createdAt', 'Ajouté le')->onlyOnIndex();
    }
}
