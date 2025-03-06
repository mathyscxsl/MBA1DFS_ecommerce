<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Smartphone Samsung', 'price' => 799.99],
            ['name' => 'Ordinateur Portable Dell', 'price' => 1299.99],
            ['name' => 'Casque Audio Bose', 'price' => 199.99],
            ['name' => 'Montre Connectée Apple', 'price' => 499.99],
            ['name' => 'Tablette iPad', 'price' => 899.99],
        ];

        foreach ($products as $data) {
            $product = new Produit();
            $product->setNom($data['name']);
            $product->setPrix($data['price']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
