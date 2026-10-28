<?php

namespace App\Validator;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MaxFeaturedValidator extends ConstraintValidator
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function validate(mixed $product, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxFeatured) {
            throw new UnexpectedTypeException($constraint, MaxFeatured::class);
        }
        if (!$product instanceof Product || !$product->isFeatured()) {
            return;
        }

        // Nombre de produits déjà à la une, sans compter le produit en cours de modification
        $qb = $this->products->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.featured = true');
        if ($product->getId()) {
            $qb->andWhere('p.id != :id')->setParameter('id', $product->getId());
        }
        $count = (int) $qb->getQuery()->getSingleScalarResult();

        if ($count >= $constraint->max) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ max }}', (string) $constraint->max)
                ->atPath('featured')
                ->addViolation();
        }
    }
}
