<?php

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ProductOption;
use PHPUnit\Framework\TestCase;

/**
 * Choix de l'image à afficher : image principale, ou image liée à l'option choisie.
 */
class ProductImageTest extends TestCase
{
    public function testMainImageIsTheOneWithTheLowestPosition(): void
    {
        $product = new Product();
        $product->addImage((new ProductImage())->setFilename('vue-2.jpg')->setPosition(2));
        $product->addImage((new ProductImage())->setFilename('principale.jpg')->setPosition(0));

        $this->assertSame('principale.jpg', $product->getMainImage());
    }

    public function testOptionImageIsUsedWhenItExists(): void
    {
        $product = new Product();
        $black = (new ProductOption())->setType('Couleur')->setValue('Noir');
        $white = (new ProductOption())->setType('Couleur')->setValue('Blanc');
        $product->addOption($black)->addOption($white);
        $product->addImage((new ProductImage())->setFilename('noir.jpg')->setPosition(0));
        $product->addImage((new ProductImage())->setFilename('blanc.jpg')->setPosition(1)->setProductOption($white));

        $this->assertSame('blanc.jpg', $product->getImageFor($white));
        // pas d'image dédiée au noir : on retombe sur l'image principale
        $this->assertSame('noir.jpg', $product->getImageFor($black));
    }

    public function testProductWithoutImageReturnsNull(): void
    {
        $this->assertNull((new Product())->getMainImage());
    }
}
