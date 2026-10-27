<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Mailer\OrderMailer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

/**
 * Envoie un email au client quand le statut de sa commande change
 * (par exemple quand l'administrateur la passe en « Expédiée » dans EasyAdmin).
 */
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Order::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Order::class)]
class OrderStatusListener
{
    /** @var array<int, true> commandes dont le statut vient de changer */
    private array $changed = [];

    public function __construct(
        private readonly OrderMailer $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function preUpdate(Order $order, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('status')) {
            $this->changed[$order->getId()] = true;
        }
    }

    // L'email part APRÈS l'enregistrement en base (postUpdate)
    public function postUpdate(Order $order, PostUpdateEventArgs $args): void
    {
        if (!isset($this->changed[$order->getId()])) {
            return;
        }
        unset($this->changed[$order->getId()]);

        try {
            $this->mailer->sendStatusUpdate($order);
        } catch (\Throwable $e) {
            $this->logger->error('Email de changement de statut non envoyé : '.$e->getMessage());
        }
    }
}
