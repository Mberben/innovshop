<?php

namespace App\Controller\Admin;

use App\Entity\ProductImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class ProductImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image')
            ->setEntityLabelInPlural('Images produits')
            ->setDefaultSort(['product' => 'ASC', 'position' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('product');
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('product', 'Produit');
        yield ImageField::new('filename', 'Image')
            ->setBasePath('uploads/products')
            ->setUploadDir('public/uploads/products')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            // obligatoire à la création, facultatif en modification
            ->setRequired(Crud::PAGE_NEW === $pageName);
        yield IntegerField::new('position', 'Ordre')
            ->setHelp('0 = image principale, puis 1, 2, 3…');
    }
}
