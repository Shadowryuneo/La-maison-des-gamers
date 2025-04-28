<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Produits;
use App\Repository\PanierRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/panier')]
final class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index')]
    public function index(SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        // $sessionId = $session->getId();
        $panier = $session->get("panier", []); // on récupere la 'variable' panier, si il n'y a rien,ca enverra un tableau vide
        $dataPanier = []; // tableau pour recuperer le produit et sa quantite dans la tableau associatif, key=>valeur
        $total = 0; // instanciation de la variable pour calculer le prix total

        foreach ($panier as $id => $quantity) { // recuper les infos du nombre dans le tableau associatif panier de la session
            $product = $produitRepository->find($id); // $id représente l'id envoyé du produit; et comme c'est l'id d'un produit, on doit trouver son id correspondant avec product->find($id), qui est une requete pour trouver l'id, et product represente le repository de product
            $dataPanier[] = [
                "product" => $product,
                "quantity" => $quantity
            ];
            $total += $product->getPrix() * $quantity;


        }

        return $this->render('cart/index.html.twig', [
            'dataPanier' => $dataPanier,
            "total" => $total,
            // 'sessionId' => $sessionId,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, SessionInterface $session, Produit $produit): Response
    {
        $panier  = $session->get('panier', []);
        $id = $produit->getId();
        $quantity = (int) $request->request->get('quantity', 1); // On récupère la quantité envoyée, ou 1 par défaut

        if (!empty($panier[$id])) {
            $panier[$id] += $quantity;
        } else {
            $panier[$id] = $quantity;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/supp/{id}', name: 'app_suppanier')]
    public function sup(int $id, SessionInterface $session): Response

    {
        $panier  = $session->get('panier', []); // on crée la 'variable' panier

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }
        $session->set('panier', $panier); // on envoit les données dans le panier

        return $this->redirectToRoute('app_cart_index');
    }


    #[Route('/remove/{id}', name: 'app_removepanier')]
    public function remove(int $id, SessionInterface $session): Response

    {
        $panier  = $session->get('panier', []); // on crée la 'variable' panier

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier); // on envoit les données dans le panier

        return $this->redirectToRoute('app_cart_index');
    }



    #[Route('/trash', name: 'app_trashpanier')]
    public function trash(SessionInterface $session): Response

    {
        $session->remove('panier');

        return $this->redirectToRoute('app_cart_index');
    }
}
