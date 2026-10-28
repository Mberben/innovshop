<?php

namespace App\Tests\Functional;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Règles d'accès : visiteur, client, administrateur.
 */
class AccessControlTest extends WebTestCase
{
    public function testVisitorIsRedirectedToLoginForProtectedPages(): void
    {
        $client = static::createClient();

        foreach (['/compte', '/commande/livraison', '/admin'] as $url) {
            $client->request('GET', $url);
            $this->assertResponseRedirects('/login', null, sprintf('%s doit rediriger vers la connexion', $url));
        }
    }

    public function testCustomerCannotAccessBackOffice(): void
    {
        $client = static::createClient();
        $client->loginUser(static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'client@innovshop.fr']));

        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessBackOffice(): void
    {
        $client = static::createClient();
        $client->loginUser(static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@innovshop.fr']));

        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful();
    }

    public function testCustomerCannotSeeAnotherCustomersOrder(): void
    {
        $client = static::createClient();
        $users = static::getContainer()->get(UserRepository::class);
        $julie = $users->findOneBy(['email' => 'client@innovshop.fr']);
        $thomas = $users->findOneBy(['email' => 'thomas@exemple.fr']);
        $thomasOrder = static::getContainer()->get(OrderRepository::class)->findOneBy(['user' => $thomas]);

        $client->loginUser($julie);

        // sa propre commande : OK
        $ownOrder = static::getContainer()->get(OrderRepository::class)->findOneBy(['user' => $julie]);
        $client->request('GET', '/compte/commandes/'.$ownOrder->getReference());
        $this->assertResponseIsSuccessful();

        // la commande d'un autre client : refusée par l'OrderVoter
        $client->request('GET', '/compte/commandes/'.$thomasOrder->getReference());
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddToCartReturnsJsonForAjaxCalls(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/produit/camera-guard-2k');
        $form = $crawler->filter('form[data-cart-add]')->form();

        $client->request('POST', $form->getUri(), $form->getValues(), [], ['HTTP_ACCEPT' => 'application/json']);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertSame(1, $data['count']);
    }
}
