<?php

// Déclaration du namespace du contrôleur
namespace App\Controller;

// Importation des entités et classes nécessaires
use App\Entity\Panier;
// use App\Entity\Commande;
// use App\Entity\Produit;
use App\Repository\PanierRepository;
// use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/panier')] // Tous les chemins de ce contrôleur commenceront par /panier
final class CartController extends AbstractController
{
    /**
     * Affiche le panier de l'utilisateur, qu'il soit connecté ou non
     */
    #[Route('/', name: 'app_cart_index')]
    public function index(SessionInterface $session, ProduitRepository $produitRepository, PanierRepository $panierRepository, Security $security): Response
    {
        $user = $security->getUser(); // Récupère l'utilisateur connecté (null s'il est non connecté)
        $dataPanier = []; // Initialisation d’un tableau pour stocker les produits + quantités
        $total = 0; // Initialisation du montant total du panier

        if ($user) {
            // Si l'utilisateur est connecté, on va chercher ses produits en base
            $paniers = $panierRepository->findBy(['utilisateurs' => $user]);

            foreach ($paniers as $panier) {
                $product = $panier->getProduit(); // Récupère le produit lié au panier
                $quantity = $panier->getQuantity(); // Quantité de ce produit

                // On ajoute l'élément au tableau à envoyer à la vue
                $dataPanier[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];

                // Calcul du total (prix * quantité)
                $total += $product->getPrix() * $quantity;
            }
        } else {
            // Si l'utilisateur n'est pas connecté → on utilise la session
            $panier = $session->get("panier", []); // on récupère le panier depuis la session

            foreach ($panier as $id => $quantity) {
                $product = $produitRepository->find($id); // on trouve le produit correspondant à l'ID
                if ($product) {
                    $dataPanier[] = [
                        "product" => $product,
                        "quantity" => $quantity
                    ];
                    $total += $product->getPrix() * $quantity;
                }
            }
        }

        // On retourne la vue du panier avec les données à afficher
        return $this->render('cart/index.html.twig', [
            'dataPanier' => $dataPanier,
            "total" => $total,
        ]);
    }

    /**
     * Ajoute un produit au panier (connecté = BDD, non connecté = session)
     */
    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, SessionInterface $session, Security $security, ProduitRepository $produitRepository, EntityManagerInterface $em, PanierRepository $panierRepository): Response
    {
        $user = $security->getUser(); // Vérifie si l’utilisateur est connecté
        $quantity = (int) $request->request->get('quantity', 1); // Récupère la quantité à ajouter (via formulaire)
        $produit = $produitRepository->find($id); // Trouve le produit via son ID

        if (!$produit) {
            // Si le produit n'existe pas, on redirige
            return $this->redirectToRoute('app_cart_index');
        }

        $stock = $produit->getStock(); // Vérifie le stock du produit

        if ($user) {
            // Utilisateur connecté → on ajoute ou modifie la BDD
            $panier = $panierRepository->findOneBy(['utilisateurs' => $user, 'produit' => $produit]);

            if ($panier) {
                // Si le produit est déjà dans le panier, on met à jour la quantité sans dépasser le stock
                $newQuantity = min($panier->getQuantity() + $quantity, $stock);
                $panier->setQuantity($newQuantity);
            } else {
                // Si le produit n’est pas encore dans le panier, on le crée
                $panier = new Panier();
                $panier->setUtilisateurs($user);
                $panier->setProduit($produit);
                $panier->setQuantity(min($quantity, $stock)); // On respecte le stock
                $em->persist($panier);
            }

            $em->flush(); // Enregistre en base de données
        } else {
            // Utilisateur non connecté → on utilise la session
            $panier = $session->get('panier', []);
            $currentQuantity = $panier[$id] ?? 0; // Quantité actuelle si déjà dans le panier
            $newQuantity = min($currentQuantity + $quantity, $stock); // On respecte le stock
            $panier[$id] = $newQuantity; // Mise à jour
            $session->set('panier', $panier); // Enregistrement en session
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Diminue la quantité d’un produit du panier (si 1 → supprime)
     */
    #[Route('/supp/{id}', name: 'app_suppanier')]
    public function sup(int $id, SessionInterface $session, Security $security, PanierRepository $panierRepository, ProduitRepository $produitRepository, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        if ($user) {
            // Si connecté → modification en BDD
            $produit = $produitRepository->find($id);
            if ($produit) {
                $panier = $panierRepository->findOneBy(['utilisateurs' => $user, 'produit' => $produit]);
                if ($panier) {
                    if ($panier->getQuantity() > 1) {
                        $panier->setQuantity($panier->getQuantity() - 1);
                    } else {
                        $em->remove($panier); // Si quantité = 1 → on supprime le panier
                    }
                    $em->flush();
                }
            }
        } else {
            // Non connecté → modification en session
            $panier = $session->get('panier', []);
            if (!empty($panier[$id])) {
                if ($panier[$id] > 1) {
                    $panier[$id]--;
                } else {
                    unset($panier[$id]); // Quantité à 0 → on supprime
                }
            }
            $session->set('panier', $panier);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Supprime complètement un produit du panier
     */
    #[Route('/remove/{id}', name: 'app_removepanier')]
    public function remove(int $id, SessionInterface $session, Security $security, PanierRepository $panierRepository, ProduitRepository $produitRepository, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        if ($user) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $panier = $panierRepository->findOneBy(['utilisateurs' => $user, 'produit' => $produit]);
                if ($panier) {
                    $em->remove($panier); // On supprime directement
                    $em->flush();
                }
            }
        } else {
            $panier = $session->get('panier', []);
            if (!empty($panier[$id])) {
                unset($panier[$id]); // Supprime depuis session
            }
            $session->set('panier', $panier);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    /**
     * Vide complètement le panier de l'utilisateur
     */
    #[Route('/trash', name: 'app_trashpanier')]
    public function trash(SessionInterface $session, Security $security, PanierRepository $panierRepository, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();

        if ($user) {
            $paniers = $panierRepository->findBy(['utilisateurs' => $user]);
            foreach ($paniers as $item) {
                $em->remove($item); // On supprime chaque ligne du panier
            }
            $em->flush(); // On valide les suppressions
        } else {
            $session->remove('panier'); // Vide le tableau panier de la session
        }

        return $this->redirectToRoute('app_cart_index');
    }
}