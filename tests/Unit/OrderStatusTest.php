<?php

namespace App\Tests\Unit;

use App\Enum\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function testEveryStatusHasAFrenchLabelAndAColor(): void
    {
        foreach (OrderStatus::cases() as $status) {
            $this->assertNotEmpty($status->label());
            $this->assertContains($status->color(), ['warning', 'info', 'success', 'danger']);
        }
    }

    public function testStoredValueMatchesTheEnumCase(): void
    {
        // C'est cette valeur que Doctrine écrit dans la colonne « status »
        $this->assertSame(OrderStatus::SHIPPED, OrderStatus::from('shipped'));
        $this->assertSame('Expédiée', OrderStatus::SHIPPED->label());
    }
}
