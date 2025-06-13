<?php

namespace App\Controller;

// use PhpParser\Node\Name;
use App\Entity\Avis;
// use App\Entity\Utilisateurs;
use App\Form\AvisType;
use App\Entity\Produit;
// use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Categories;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
public function index(ProduitRepository $produitRepository): Response
{
    $produits = $produitRepository->findBy(['status' => true]);

    // Regroupement manuel par catégorie
    $produitsParCategorie = [];
    foreach ($produits as $produit) {
        $categorie = $produit->getCategorie()->getNom(); // ou ->getId() selon ton modèle
        $produitsParCategorie[$categorie][] = $produit;
    }

    return $this->render('home/index.html.twig', [
        'produitsParCategorie' => $produitsParCategorie,
    ]);
}

    #[Route('/mention_legal', name: 'app_mention_legal')]
    public function mention(): Response
    {
        return $this->render('footer/mention_legal.html.twig');
    }

    #[Route('/categorie/{id}', name: 'app_categorie_id')]
    public function categoriesCategorie(Categories $category, ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findBy(['status' => true, 'categorie' => $category]);
        return $this->render('home/categorie.html.twig', [
            'produits' => $produits,
            'categorie' => $category
        ]);
    }

    #[Route('/detail/{id}', name: 'app_categorie_produit')]
    public function produitFiche(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        $message = new Avis();
        $form = $this->createForm(AvisType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $message->setUtilisateurs($this->getUser());
            $message->setProduit($produit);
            $message->setCreatedAt(new \DateTimeImmutable('now'));

            $em->persist($message);
            $em->flush();
            $this->addFlash('success', 'Merci pour votre commentaire, il sera traîté dans les plus brefs délais');
            return $this->redirectToRoute('app_categorie_produit', ['id' => $produit->getId()]);
        }

        return $this->render('home/produit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView()
        ]);
    }

    // #[Route('/compte', name: 'app_mon_compte')]
    // public function monCompte(Security $security): Response
    // {
    //     $utilisateur = $security->getUser();
    //     if (!$utilisateur) {
    //         throw $this->createNotFoundException('Utilisateur non trouvé');
    //     }
    //     return $this->render('home/compte.html.twig', [
    //         'utilisateur' => $utilisateur,
    //     ]);
    // }
}
