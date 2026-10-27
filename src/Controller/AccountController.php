<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use App\Repository\OrderRepository;
use App\Security\Voter\OrderVoter;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Espace client : réservé aux utilisateurs connectés.
 */
#[Route('/compte')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('', name: 'app_account')]
    public function index(OrderRepository $orders): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'lastOrders' => $orders->findBy(['user' => $user], ['createdAt' => 'DESC'], 3),
            'orderCount' => $orders->count(['user' => $user]),
            'spentThisMonth' => $orders->sumSpentThisMonth($user),
        ]);
    }

    #[Route('/commandes', name: 'app_account_orders')]
    public function orders(OrderRepository $orders): Response
    {
        return $this->render('account/orders.html.twig', [
            'orders' => $orders->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/commandes/{reference}', name: 'app_account_order_show')]
    #[IsGranted(OrderVoter::VIEW, subject: 'order')] // un client ne voit que ses commandes
    public function orderShow(#[MapEntity(mapping: ['reference' => 'reference'])] Order $order): Response
    {
        return $this->render('account/order_show.html.twig', [
            'order' => $order,
            'steps' => [OrderStatus::PENDING, OrderStatus::VALIDATED, OrderStatus::SHIPPED],
        ]);
    }

    #[Route('/commandes/{reference}/facture', name: 'app_account_invoice')]
    #[IsGranted(OrderVoter::VIEW, subject: 'order')]
    public function invoice(#[MapEntity(mapping: ['reference' => 'reference'])] Order $order): Response
    {
        // Le HTML de la facture est généré par Twig, puis converti en PDF par Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // gère les accents et le symbole €
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->renderView('account/invoice.html.twig', ['order' => $order]));
        $dompdf->setPaper('A4');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="facture-%s.pdf"', $order->getReference()),
        ]);
    }

    #[Route('/profil', name: 'app_account_profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProfileType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Vos informations ont été mises à jour.');

            return $this->redirectToRoute('app_account_profile');
        }

        return $this->render('account/profile.html.twig', ['form' => $form]);
    }

    #[Route('/mot-de-passe', name: 'app_account_password')]
    public function password(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('newPassword')->getData()));
            $em->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié.');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/password.html.twig', ['form' => $form]);
    }
}
