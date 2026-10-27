<?php

namespace App\Controller\Admin;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly ProductRepository $products,
        private readonly UserRepository $users,
    ) {
    }

    public function index(): Response
    {
        // Tableau de bord : chiffres clés + ventes des 14 derniers jours
        $revenue = $this->orders->revenueByDay(14);

        return $this->render('admin/dashboard.html.twig', [
            'revenue' => $revenue,
            'revenueTotal' => array_sum($revenue),
            'maxDay' => max(1, ...array_values($revenue)),
            'pendingCount' => $this->orders->count(['status' => OrderStatus::PENDING]),
            'orderCount' => $this->orders->count([]),
            'productCount' => $this->products->count([]),
            'customerCount' => $this->users->count([]),
            'lastOrders' => $this->orders->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('InnovShop')
            ->setLocales(['fr']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-chart-line');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkTo(ProductCrudController::class, 'Produits', 'fa fa-box');
        yield MenuItem::linkTo(ProductImageCrudController::class, 'Images', 'fa fa-image');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-tags');

        yield MenuItem::section('Ventes');
        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fa fa-cart-shopping');
        yield MenuItem::linkTo(UserCrudController::class, 'Clients', 'fa fa-users');

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-store', '/');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-right-from-bracket');
    }
}