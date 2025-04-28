<?php

// namespace App\Service;


// use App\Repository\ProduitRepository;
// use Symfony\Component\HttpFoundation\Session\SessionInterface;

// class CartService
// {
//     private $session;
//     private $produitRepository;

//     public function __construct(SessionInterface $session, ProduitRepository $produitRepository)
//     {
//         $this->session = $session;
//         $this->produitRepository = $produitRepository;
//     }

//     public function getCart(): array
//     {
//         $cartData = $this->session->get('cart', []);
//         $cart = [];

//         foreach ($cartData as $produitId => $quantity) {
//             $produit = $this->produitRepository->find($produitId);
//             if ($produit) {
//                 $cart[] = [
//                     'produit' => $produit,
//                     'quantite' => $quantity
//                 ];
//             }
//         }

//         return $cart;
//     }

//     public function addToCart($produitId, $quantity): void
//     {
//         $cart = $this->getRawCart(); 

//         if (isset($cart[$produitId])) {
//             $cart[$produitId] += $quantity;
//         } else {
//             $cart[$produitId] = $quantity;
//         }

//         $this->session->set('cart', $cart);
//     }

//     public function removeFromCart($produitId): void
//     {
//         $cart = $this->getRawCart();

//         if (isset($cart[$produitId])) {
//             unset($cart[$produitId]);
//         }

//         $this->session->set('cart', $cart);
//     }

//     public function cleanCart(): void
//     {
//         $this->session->remove('cart');
//     }

   
//     private function getRawCart(): array
//     {
//         return $this->session->get('cart', []);
//     }
// }