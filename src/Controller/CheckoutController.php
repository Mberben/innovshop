<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Form\ShippingType;
use App\Mailer\OrderMailer;
use App\Security\Voter\OrderVoter;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tunnel de commande en 3 étapes :
 *   1. Panier (CartController)  2. Livraison  3. Confirmation + enregistrement
 * Réservé aux utilisateurs connectés (IsGranted + access_control sur ^/commande).
 */
#[Route('/commande')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    private const SESSION_SHIPPING = 'checkout_shipping';

    public function __construct(private readonly CartService $cart)
    {
    }

    #[Route('/livraison', name: 'app_checkout_shipping')]
    public function shipping(Request $request, EntityManagerInterface $em): Response
    {
        if ($this->cart->isEmpty()) {
            return $this->redirectToRoute('app_cart');
        }

        /** @var User $user */
        $user = $this->getUser();

        // Pré-remplissage : adresse déjà saisie dans cette commande, sinon celle du profil
        $order = $this->buildShippingOrder($request) ?? (new Order())
            ->setShippingName(trim($user->getFirstName().' '.$user->getLastName()))
            ->setShippingAddress((string) $user->getAddress())
            ->setShippingPostalCode((string) $user->getPostalCode())
            ->setShippingCity((string) $user->getCity())
            ->setShippingPhone((string) $user->getPhone());

        $form = $this->createForm(ShippingType::class, $order, ['validation_groups' => ['Default']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->set(self::SESSION_SHIPPING, [
                'name' => $order->getShippingName(),
                'address' => $order->getShippingAddress(),
                'postalCode' => $order->getShippingPostalCode(),
                'city' => $order->getShippingCity(),
                'phone' => $order->getShippingPhone(),
            ]);

            if ($form->get('saveAddress')->getData()) {
                $user->setAddress($order->getShippingAddress())
                    ->setPostalCode($order->getShippingPostalCode())
                    ->setCity($order->getShippingCity())
                    ->setPhone($order->getShippingPhone());
                $em->flush();
            }

            return $this->redirectToRoute('app_checkout_confirm');
        }

        return $this->render('checkout/shipping.html.twig', [
            'form' => $form,
            'total' => $this->cart->getTotal(),
            'count' => $this->cart->count(),
        ]);
    }

    #[Route('/confirmation', name: 'app_checkout_confirm', methods: ['GET'])]
    public function confirm(Request $request): Response
    {
        if ($this->cart->isEmpty()) {
            return $this->redirectToRoute('app_cart');
        }
        if (!$shipping = $this->buildShippingOrder($request)) {
            return $this->redirectToRoute('app_checkout_shipping');
        }

        return $this->render('checkout/confirm.html.twig', [
            'lines' => $this->cart->getLines(),
            'total' => $this->cart->getTotal(),
            'shipping' => $shipping,
        ]);
    }

    #[Route('/valider', name: 'app_checkout_place', methods: ['POST'])]
    public function place(Request $request, EntityManagerInterface $em, OrderMailer $mailer, LoggerInterface $logger): Response
    {
        if (!$this->isCsrfTokenValid('checkout_place', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $order = $this->buildShippingOrder($request);
        if ($this->cart->isEmpty() || !$order) {
            return $this->redirectToRoute('app_cart');
        }

        /** @var User $user */
        $user = $this->getUser();
        $order->setUser($user)
            ->setStatus(OrderStatus::PENDING)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setReference(sprintf('CMD-%s-%s', date('Ymd'), strtoupper(bin2hex(random_bytes(3)))));

        // Chaque ligne du panier devient une ligne de commande.
        // Nom, option et prix sont COPIÉS : l'historique reste juste même si le catalogue change.
        $total = 0;
        foreach ($this->cart->getLines() as $line) {
            $product = $line['product'];
            $order->addOrderLine((new OrderLine())
                ->setProduct($product)
                ->setProductOption($line['option'])
                ->setProductName($product->getName())
                ->setOptionLabel($line['option'] ? (string) $line['option'] : null)
                ->setPrice($product->getPrice()));
            $total += $product->getPrice();
        }
        $order->setTotal($total);

        $em->persist($order);
        $em->flush();

        $this->cart->clear();
        $request->getSession()->remove(self::SESSION_SHIPPING);

        // Un problème d'envoi d'email ne doit pas faire échouer la commande
        try {
            $mailer->sendConfirmation($order);
        } catch (\Throwable $e) {
            $logger->error('Email de confirmation non envoyé : '.$e->getMessage());
        }

        return $this->redirectToRoute('app_checkout_success', ['reference' => $order->getReference()]);
    }

    #[Route('/merci/{reference}', name: 'app_checkout_success', methods: ['GET'])]
    #[IsGranted(OrderVoter::VIEW, subject: 'order')]
    public function success(#[MapEntity(mapping: ['reference' => 'reference'])] Order $order): Response
    {
        return $this->render('checkout/success.html.twig', ['order' => $order]);
    }

    /** Reconstruit une commande (non enregistrée) à partir de l'adresse gardée en session */
    private function buildShippingOrder(Request $request): ?Order
    {
        $data = $request->getSession()->get(self::SESSION_SHIPPING);
        if (!$data) {
            return null;
        }

        return (new Order())
            ->setShippingName($data['name'])
            ->setShippingAddress($data['address'])
            ->setShippingPostalCode($data['postalCode'])
            ->setShippingCity($data['city'])
            ->setShippingPhone($data['phone']);
    }
}
