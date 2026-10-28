<?php

namespace App\Tests\Unit;

use App\Service\CartService;
use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;

/**
 * Les prix sont stockés en centimes : le filtre Twig « price » doit les afficher en euros.
 */
class PriceFilterTest extends TestCase
{
    private AppExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new AppExtension($this->createStub(CartService::class));
    }

    public function testFormatsCentsAsEuros(): void
    {
        $this->assertSame('19,99 €', $this->extension->formatPrice(1999));
    }

    public function testFormatsThousandsWithSpace(): void
    {
        $this->assertSame('1 249,00 €', $this->extension->formatPrice(124900));
    }

    public function testNullPriceIsZero(): void
    {
        $this->assertSame('0,00 €', $this->extension->formatPrice(null));
    }
}
