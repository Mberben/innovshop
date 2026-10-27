<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Total dépensé par un client depuis le 1er du mois (en centimes), commandes annulées exclues.
     * Sert au suivi de l'objectif de dépenses dans l'espace client.
     */
    public function sumSpentThisMonth(User $user): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COALESCE(SUM(o.total), 0)')
            ->andWhere('o.user = :user')
            ->andWhere('o.createdAt >= :start')
            ->andWhere('o.status != :cancelled')
            ->setParameter('user', $user)
            ->setParameter('start', new \DateTimeImmutable('first day of this month midnight'))
            ->setParameter('cancelled', OrderStatus::CANCELLED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Chiffre d'affaires (centimes) par jour sur les N derniers jours, pour le tableau de bord admin */
    public function revenueByDay(int $days = 14): array
    {
        $start = new \DateTimeImmutable(sprintf('-%d days midnight', $days - 1));
        $orders = $this->createQueryBuilder('o')
            ->andWhere('o.createdAt >= :start')
            ->andWhere('o.status != :cancelled')
            ->setParameter('start', $start)
            ->setParameter('cancelled', OrderStatus::CANCELLED)
            ->getQuery()
            ->getResult();

        $byDay = [];
        for ($i = 0; $i < $days; ++$i) {
            $byDay[$start->modify("+$i days")->format('Y-m-d')] = 0;
        }
        foreach ($orders as $order) {
            $byDay[$order->getCreatedAt()->format('Y-m-d')] += $order->getTotal();
        }

        return $byDay;
    }
}
