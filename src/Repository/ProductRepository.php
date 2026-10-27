<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /** Les N derniers produits ajoutés */
    public function findLatest(int $limit = 3): array
    {
        return $this->findBy([], ['createdAt' => 'DESC'], $limit);
    }

    /** Les N derniers produits « à la une » */
    public function findFeatured(int $limit = 3): array
    {
        return $this->findBy(['featured' => true], ['createdAt' => 'DESC'], $limit);
    }

    /**
     * Recherche du catalogue : mot-clé, catégorie, fourchette de prix (en euros) et tri.
     * Les images et la catégorie sont chargées dans la même requête (évite les requêtes N+1).
     *
     * @return Product[]
     */
    public function search(?string $q, ?string $categorySlug, ?int $minEuros, ?int $maxEuros, string $sort = 'recent'): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')->addSelect('c')
            ->leftJoin('p.images', 'i')->addSelect('i');

        if ($q) {
            $qb->andWhere('p.name LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }
        if ($categorySlug) {
            $qb->andWhere('c.slug = :slug')->setParameter('slug', $categorySlug);
        }
        if (null !== $minEuros) {
            $qb->andWhere('p.price >= :min')->setParameter('min', $minEuros * 100);
        }
        if (null !== $maxEuros) {
            $qb->andWhere('p.price <= :max')->setParameter('max', $maxEuros * 100);
        }

        match ($sort) {
            'price_asc' => $qb->orderBy('p.price', 'ASC'),
            'price_desc' => $qb->orderBy('p.price', 'DESC'),
            'name' => $qb->orderBy('p.name', 'ASC'),
            default => $qb->orderBy('p.createdAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}
