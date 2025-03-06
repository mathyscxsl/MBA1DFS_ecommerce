<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

class ProduitControllerTest extends WebTestCase
{
    public function testPageAccueilAfficheProduits()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Boutique Symfony');
    }

    public function testPanierPageIsSuccessful(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/panier');

        $this->assertResponseIsSuccessful();

        // Vérifie soit la présence d'une liste <ul>, soit un message indiquant que le panier est vide
        $this->assertTrue(
            $crawler->filter('ul')->count() > 0 || $crawler->filter('p:contains("Votre panier est vide")')->count() > 0,
            'La page panier doit contenir une liste <ul> ou un message indiquant que le panier est vide.'
        );
    }

    public function testAjouterProduitAuPanier(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $lienAjouterAuPanier = $crawler->filter('a[href^="/ajouter-panier/"]')->first();
        $this->assertCount(1, $lienAjouterAuPanier, 'Le lien d\'ajout au panier n\'a pas été trouvé');

        $client->click($lienAjouterAuPanier->link());
        $this->assertResponseRedirects('/', 302);
        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Boutique Symfony');

        $lienPanier = $crawler->filter('a[href="/panier"]');
        $this->assertCount(1, $lienPanier, 'Le lien vers le panier n\'a pas été trouvé');
        $this->assertSelectorTextContains('a[href="/panier"]', 'Voir le panier');
    }

    public function testSupprimerProduitDuPanier(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $lienAjouterAuPanier = $crawler->filter('a[href^="/ajouter-panier/"]')->first();
        $client->click($lienAjouterAuPanier->link());
        $client->followRedirect();

        $crawler = $client->request('GET', '/panier');
        $this->assertResponseIsSuccessful();

        $this->assertCount(1, $crawler->filter('.cart-item'), 'Aucun produit trouvé dans le panier');

        $lienSupprimerDuPanier = $crawler->filter('a[href^="/supprimer-panier/"]')->first();
        $this->assertCount(1, $lienSupprimerDuPanier, 'Le lien de suppression du panier n\'a pas été trouvé');
        $client->click($lienSupprimerDuPanier->link());

        $this->assertResponseRedirects('/panier', 302);

        $client->followRedirect();
        $this->assertSelectorExists('.cart-header');
        $this->assertSelectorTextContains('h1', 'Votre Panier');
    }

    public function testTotalPanierCorrect(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $session = $client->getContainer()->get('session.factory')->createSession();
        $session->set('panier', []);
        $session->save();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $produit = $entityManager->getRepository(Produit::class)->findOneBy([]);

        $this->assertNotNull($produit, "Aucun produit trouvé dans la base de données");

        $expectedTotal = $produit->getPrix();

        $client->request('GET', '/ajouter-panier/' . $produit->getId());
        $this->assertResponseRedirects('/', 302);

        $crawler = $client->request('GET', '/panier');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('.cart-item', 'Le panier est vide');

        $totalElement = $crawler->filter('.cart-total strong');
        $this->assertCount(1, $totalElement, 'Élément du total non trouvé');

        $totalText = $totalElement->text();
        preg_match('/(\d+(?:[.,]\d+)?)/', $totalText, $matches);
        $actualTotal = floatval(str_replace(',', '.', $matches[0]));

        $this->assertEquals(
            round($expectedTotal, 2),
            round($actualTotal, 2),
            "Le total du panier est incorrect. Attendu: {$expectedTotal}€, Reçu: {$actualTotal}€"
        );
    }
}
