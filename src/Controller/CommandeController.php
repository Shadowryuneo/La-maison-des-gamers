<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Entity\Commande;
use App\Entity\Detailcommande;
use App\Service\MailerService;
use App\Form\AdresseLivraisonType;
use App\Form\AdresseFacturationType;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Stripe\Exception\ApiErrorException;
use App\Entity\Adresselivraisoncommande;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Adressefacturationcommande;
use Symfony\Bundle\SecurityBundle\Security;
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CommandeController extends AbstractController
{
    private $stripeSecretKey;
    private $logger;

    // Injection du logger et récupération de la clé Stripe depuis l'environnement
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->stripeSecretKey = $_ENV['STRIPE_SECRET_KEY']; // Clé Stripe récupérée de .env.local
    }

    #[Route('/commande/valider', name: 'commande_valider')]
    public function valider(Request $request, Security $security, EntityManagerInterface $em, PanierRepository $panierRepository, ProduitRepository $produitRepository): Response
    {
        $utilisateur = $security->getUser();

        // Vérifie si l'utilisateur est connecté
        if (!$utilisateur) {
            return $this->redirectToRoute('app_cart_index');
        }

        // Récupère le panier de l'utilisateur
        $panier = $panierRepository->findBy(['utilisateurs' => $utilisateur]);

        // Vérifie si le panier est vide
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

        // Création de la commande (non payée)
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

        // Sauvegarder la commande sans lier le paiement pour l'instant
        $em->persist($commande);
        $em->flush();

        // Vider le panier de l'utilisateur après la création de la commande
        foreach ($panier as $panierProduit) {
            $em->remove($panierProduit);  // Supprimer les produits du panier
        }
        $em->flush();  // Valider les changements

        // Créer la session Stripe pour le paiement
        Stripe::setApiKey($this->stripeSecretKey);

        $checkoutSession = null;
        try {
            // Créer une session de paiement Stripe
            $successUrl = $this->generateUrl('commande_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('commande_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => array_map(function ($panierProduit) {
                    return [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $panierProduit->getProduit()->getNom(),
                            ],
                            'unit_amount' => (int)($panierProduit->getProduit()->getPrix() * 100),
                        ],
                        'quantity' => $panierProduit->getQuantity(),
                    ];
                }, $panier),
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        } catch (ApiErrorException $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session Stripe: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart_index');
        }

        // Rediriger vers la page de paiement Stripe
        return $this->redirect($checkoutSession->url);
    }
    #[Route('/commande/success/{id}', name: 'commande_success')]
    public function success(Commande $commande, EntityManagerInterface $em, MailerService $mailer): Response
    {
        $commande->setIsPaid(true);
        $em->persist($commande);
        $em->flush();

        // Envoi de l'e-mail de confirmation
        $mailer->envoyerConfirmationCommande(
            $commande->getUtilisateurs()->getEmail(),
            'Confirmation de votre commande',
            ['commande' => $commande]
        );

        return $this->render('commande/success.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/cancel', name: 'commande_cancel')]
    public function cancel(): Response
    {
        // Si l'utilisateur annule la commande sur Stripe, on le redirige vers la page d'accueil ou panier
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/commande/webhook', name: 'commande_webhook', methods: ['POST'])]
    public function webhook(Request $request, EntityManagerInterface $em)
    {
        Stripe::setApiKey($this->stripeSecretKey);

        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        $event = null;

        try {
            // Vérifier la signature et récupérer l'événement Stripe
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $_ENV['STRIPE_ENDPOINT_SECRET']);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error('Erreur lors de la réception du webhook : ' . $e->getMessage());
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->logger->error('Erreur de signature dans le webhook Stripe : ' . $e->getMessage());
            return new Response('Invalid signature', 400);
        }

        // Traiter les événements de paiement réussi
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Récupérer la commande associée à la session Stripe
            $commandeId = $session->metadata->commande_id;
            $commande = $em->getRepository(Commande::class)->find($commandeId);

            // Marquer la commande comme payée
            if ($commande) {
                $commande->setIsPaid(true);
                $em->persist($commande);
                $em->flush();
            } else {
                $this->logger->error('Commande non trouvée pour l\'ID ' . $commandeId);
            }
        }

        return new Response('Webhook received', 200);
    }
}