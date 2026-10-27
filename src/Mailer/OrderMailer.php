<?php

namespace App\Mailer;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Regroupe les emails liés aux commandes (logique hors des contrôleurs).
 */
class OrderMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $sender,
    ) {
    }

    /** Récapitulatif envoyé juste après la validation de la commande */
    public function sendConfirmation(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->sender, 'InnovShop'))
            ->to(new Address($order->getUser()->getEmail(), (string) $order->getUser()))
            ->subject(sprintf('Votre commande %s est confirmée', $order->getReference()))
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context(['order' => $order]);

        $this->mailer->send($email);
    }

    /** Notification envoyée quand l'administrateur change le statut */
    public function sendStatusUpdate(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->sender, 'InnovShop'))
            ->to(new Address($order->getUser()->getEmail(), (string) $order->getUser()))
            ->subject(sprintf('Commande %s : %s', $order->getReference(), $order->getStatus()->label()))
            ->htmlTemplate('emails/order_status.html.twig')
            ->context(['order' => $order]);

        $this->mailer->send($email);
    }
}
