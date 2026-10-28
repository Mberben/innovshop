<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Pages publiques : nécessitent la base de test remplie avec les fixtures.
 */
class PublicPagesTest extends WebTestCase
{
    public function testHomePageShowsFeaturedAndLatestProducts(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Produits à la une');
        // 3 produits à la une + 3 derniers produits
        $this->assertCount(6, $crawler->filter('.product-card'));
    }

    public function testCatalogueSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/catalogue?q=drone');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('.product-card')->count());
        foreach ($crawler->filter('.product-card-title') as $title) {
            $this->assertStringContainsStringIgnoringCase('drone', $title->textContent);
        }
    }

    public function testProductPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/produit/ecouteurs-pulse-air');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Écouteurs Pulse Air');
    }

    public function testUnknownProductReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/produit/produit-inexistant');

        $this->assertResponseStatusCodeSame(404);
    }
}
