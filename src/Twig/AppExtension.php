<?php

namespace App\Twig;

use App\Service\CartService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function getFilters(): array
    {
        return [
            // {{ product.price|price }} → « 129,00 € » (les prix sont stockés en centimes)
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            // {{ cart_count() }} : nombre d'articles dans le panier, affiché dans le header
            new TwigFunction('cart_count', [$this->cart, 'count']),
        ];
    }

    public function formatPrice(?int $cents): string
    {
        return number_format(($cents ?? 0) / 100, 2, ',', ' ').' €';
    }
}
