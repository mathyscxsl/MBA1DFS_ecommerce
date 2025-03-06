<?php

namespace App\Tests;

use App\Entity\Produit;
use PHPUnit\Framework\TestCase;

class ProduitTest extends TestCase
{
    public function testProduitAttributes()
    {
        $produit = new Produit();
        $produit->setNom('Smartphone Samsung');
        $produit->setPrix(799.99);

        $this->assertEquals('Smartphone Samsung', $produit->getNom());
        $this->assertEquals(799.99, $produit->getPrix());
    }
}
