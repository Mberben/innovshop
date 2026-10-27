<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Statut d'une commande.
 * TranslatableInterface permet à Symfony (formulaires, EasyAdmin, filtre |trans de Twig)
 * d'afficher automatiquement le libellé français.
 */
enum OrderStatus: string implements TranslatableInterface
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case SHIPPED = 'shipped';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::VALIDATED => 'Validée',
            self::SHIPPED => 'Expédiée',
            self::CANCELLED => 'Annulée',
        };
    }

    /** Couleur Bootstrap associée, utilisée pour les badges du site */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::VALIDATED => 'info',
            self::SHIPPED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $this->label();
    }
}
