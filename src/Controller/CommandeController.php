<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Detailcommande;
use App\Entity\Adresselivraisoncommande;
use App\Entity\Adressefacturationcommande;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AdresseLivraisonType;
use App\Form\AdresseFacturationType;

final class CommandeController extends AbstractController
{
    #[Route('/commande/valider', name: 'commande_valider')]
    public function valider(Request $request, SessionInterface $session, Security $security, EntityManagerInterface $em, PanierRepository $panierRepository, ProduitRepository $produitRepository): Response
    {
        $utilisateur = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$utilisateur) {
            return $this->redirectToRoute('app_cart_index');
        }

        // Récupérer le panier de l'utilisateur
        $panier = $panierRepository->findBy(['utilisateurs' => $utilisateur]);

        // Vérifier si le panier est vide
        if (!$panier || empty($panier)) {
            return $this->redirectToRoute('app_cart_index');
        }

        // Création des formulaires pour les adresses
        $adresseLivraison = new Adresselivraisoncommande();
        $formLivraison = $this->createForm(AdresseLivraisonType::class, $adresseLivraison);
        $formLivraison->handleRequest($request);

        $adresseFacturation = new Adressefacturationcommande();
        $formFacturation = $this->createForm(AdresseFacturationType::class, $adresseFacturation);
        $formFacturation->handleRequest($request);

        // Vérification si les formulaires sont soumis et valides
        if (!$formLivraison->isSubmitted() || !$formFacturation->isSubmitted() || !$formLivraison->isValid() || !$formFacturation->isValid()) {
            return $this->render('commande/valider.html.twig', [
                'formLivraison' => $formLivraison->createView(),
                'formFacturation' => $formFacturation->createView(),
                'panier' => $panier,
                'total' => array_reduce($panier, fn($acc, $item) => $acc + $item->getProduit()->getPrix() * $item->getQuantity(), 0)
            ]);
        }

        // Création de la commande
        $commande = new Commande();
        $commande->setUtilisateurs($utilisateur);
        $commande->setNom('Commande_' . uniqid());
        $commande->setAdresselivraisoncommande($adresseLivraison);
        $commande->setAdressefacturationcommande($adresseFacturation);

        $total = 0;

        // Parcourir le panier et ajouter les produits à la commande
        foreach ($panier as $panierProduit) {
            $produit = $produitRepository->find($panierProduit->getProduit()->getId());
            $quantite = $panierProduit->getQuantity();

            // Vérification du stock
            if ($produit->getStock() < $quantite) {
                $this->addFlash('error', 'Le produit "' . $produit->getNom() . '" n\'a pas assez de stock disponible.');
                return $this->redirectToRoute('app_cart_index');
            }

            // Création des détails de commande
            $detail = new Detailcommande();
            $detail->setCommande($commande);
            $detail->setProduit($produit);
            $detail->setQuantity($quantite);
            $detail->setPrix($produit->getPrix());

            // Persister chaque detailcommande manuellement
            $em->persist($detail);

            // Ajout du détail à la commande
            $commande->addDetailcommande($detail);

            // Mise à jour du stock
            $produit->setStock($produit->getStock() - $quantite);

            // Calcul du total
            $total += $produit->getPrix() * $quantite;
        }

        // Fixer le prix total de la commande
        $commande->setPrixtotal($total);

        // Sauvegarder la commande
        $em->persist($commande);
        $em->flush();

        // Vider le panier de l'utilisateur
        foreach ($panier as $panierProduit) {
            $em->remove($panierProduit);
        }
        $em->flush();

        // Supprimer le panier de la session
        $session->remove('panier');

        // Rediriger l'utilisateur vers la page de succès
        return $this->redirectToRoute('commande_success', ['id' => $commande->getId()]);
    }

    #[Route('/commande/success/{id}', name: 'commande_success')]
    public function success(Commande $commande): Response
    {
        return $this->render('commande/success.html.twig', [
            'commande' => $commande,
        ]);
    }
}