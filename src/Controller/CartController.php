<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductOptionRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class CartController extends AbstractController
{
    public function __construct(private readonly CartService $cart)
    {
    }

    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'lines' => $this->cart->getLines(),
            'total' => $this->cart->getTotal(),
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request, ProductOptionRepository $options): Response
    {
        if (!$this->isCsrfTokenValid('cart_add', $request->request->getString('_token'))) {
            return $this->reply($request, false, 'Session expirée, rechargez la page.', 400);
        }

        $option = null;
        if (!$product->getOptions()->isEmpty()) {
            $option = $options->find($request->request->getInt('option'));
            // l'option doit exister ET appartenir à ce produit
            if (!$option || $option->getProduct() !== $product) {
                return $this->reply($request, false, 'Veuillez choisir une option.', 422);
            }
        }

        $this->cart->add($product, $option);

        return $this->reply($request, true, sprintf('« %s » a été ajouté au panier.', $product->getName()));
    }

    #[Route('/supprimer/{lineId}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(string $lineId, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cart_remove', $request->request->getString('_token'))) {
            return $this->reply($request, false, 'Session expirée, rechargez la page.', 400);
        }

        $this->cart->remove($lineId);

        return $this->reply($request, true, 'Article retiré du panier.');
    }

    /**
     * Réponse JSON pour les appels AJAX (fetch), redirection classique sinon.
     * Le site fonctionne donc aussi sans JavaScript.
     */
    private function reply(Request $request, bool $success, string $message, int $status = 200): Response
    {
        if ('json' === $request->getPreferredFormat() || $request->isXmlHttpRequest()) {
            $total = $this->cart->getTotal();

            return new JsonResponse([
                'success' => $success,
                'message' => $message,
                'count' => $this->cart->count(),
                'total' => number_format($total / 100, 2, ',', ' ').' €',
            ], $status);
        }

        $this->addFlash($success ? 'success' : 'danger', $message);

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_cart'));
    }
}
