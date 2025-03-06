<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/', name: 'produit_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $produits = $em->getRepository(Produit::class)->findAll();
        return $this->render('produit/index.html.twig', ['produits' => $produits]);
    }

    #[Route('/panier', name: 'panier')]
    public function panier(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        $total = array_sum(array_column($panier, 'prix'));

        return $this->render('produit/panier.html.twig', ['panier' => $panier, 'total' => $total]);
    }

    #[Route('/ajouter-panier/{id}', name: 'ajouter_panier')]
    public function ajouterPanier($id, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $produit = $em->getRepository(Produit::class)->find($id);
        if ($produit) {
            $panier = $session->get('panier', []);
            $panier[] = ['id' => $produit->getId(), 'nom' => $produit->getNom(), 'prix' => $produit->getPrix()];
            $session->set('panier', $panier);
        }

        return $this->redirectToRoute('produit_index');
    }

    #[Route('/supprimer-panier/{index}', name: 'supprimer_panier')]
    public function supprimerPanier($index, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        if (isset($panier[$index])) {
            unset($panier[$index]);
            $session->set('panier', array_values($panier)); // Réindexation du tableau
        }

        return $this->redirectToRoute('panier');
    }
}

