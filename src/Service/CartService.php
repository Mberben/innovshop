<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductOption;
use App\Repository\ProductOptionRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Panier stocké en session.
 *
 * Pas de notion de quantité (exigence du sujet) : chaque ajout crée une nouvelle ligne.
 * En session, on ne garde que des identifiants ; les entités sont rechargées à la lecture
 * pour toujours afficher le prix et le nom à jour.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
        private readonly ProductOptionRepository $optionRepository,
    ) {
    }

    public function add(Product $product, ?ProductOption $option = null): void
    {
        $lines = $this->getRawLines();
        $lines[] = [
            'id' => bin2hex(random_bytes(6)), // identifiant unique de la ligne
            'product' => $product->getId(),
            'option' => $option?->getId(),
        ];
        $this->save($lines);
    }

    public function remove(string $lineId): void
    {
        $lines = array_filter($this->getRawLines(), static fn (array $line) => $line['id'] !== $lineId);
        $this->save(array_values($lines));
    }

    public function clear(): void
    {
        $this->save([]);
    }

    /**
     * @return list<array{id: string, product: Product, option: ?ProductOption}>
     */
    public function getLines(): array
    {
        $lines = [];
        foreach ($this->getRawLines() as $raw) {
            $product = $this->productRepository->find($raw['product']);
            if (!$product) {
                continue; // produit supprimé entre-temps
            }
            $option = $raw['option'] ? $this->optionRepository->find($raw['option']) : null;
            $lines[] = ['id' => $raw['id'], 'product' => $product, 'option' => $option];
        }

        return $lines;
    }

    /** Total en centimes */
    public function getTotal(): int
    {
        return array_sum(array_map(static fn (array $line) => $line['product']->getPrice(), $this->getLines()));
    }

    public function count(): int
    {
        return \count($this->getRawLines());
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    private function getRawLines(): array
    {
        $session = $this->requestStack->getSession();

        return $session->get(self::SESSION_KEY, []);
    }

    private function save(array $lines): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $lines);
    }
}
