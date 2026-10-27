<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalogue')]
    public function index(Request $request, ProductRepository $products, CategoryRepository $categories): Response
    {
        // Filtres lus dans l'URL : /catalogue?q=drone&categorie=drones&min=50&max=500&tri=price_asc
        $q = trim((string) $request->query->get('q', '')) ?: null;
        $category = $request->query->get('categorie') ?: null;
        $min = '' !== (string) $request->query->get('min', '') ? max(0, $request->query->getInt('min')) : null;
        $max = '' !== (string) $request->query->get('max', '') ? max(0, $request->query->getInt('max')) : null;
        $sort = $request->query->get('tri', 'recent');

        return $this->render('product/index.html.twig', [
            'products' => $products->search($q, $category, $min, $max, $sort),
            'categories' => $categories->findBy([], ['name' => 'ASC']),
            'filters' => ['q' => $q, 'categorie' => $category, 'min' => $min, 'max' => $max, 'tri' => $sort],
        ]);
    }

    #[Route('/produit/{slug}', name: 'app_product_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Product $product, ProductRepository $products): Response
    {
        // Produits de la même catégorie, pour la section « Vous aimerez aussi »
        $related = $product->getCategory()
            ? array_filter(
                $products->findBy(['category' => $product->getCategory()], ['createdAt' => 'DESC'], 4),
                static fn (Product $p) => $p->getId() !== $product->getId()
            )
            : [];

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'related' => \array_slice($related, 0, 3),
        ]);
    }
}
