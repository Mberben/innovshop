<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Limite le nombre de produits « à la une » (l'accueil en affiche 3).
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxFeatured extends Constraint
{
    public function __construct(
        public int $max = 3,
        public string $message = 'Il y a déjà {{ max }} produits à la une. Retirez-en un avant d\'en ajouter un autre.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
