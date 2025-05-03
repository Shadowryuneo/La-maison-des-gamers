<?php

namespace App\Security;

use App\Entity\Panier;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class EmailAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, private ProduitRepository $produitRepository , private EntityManagerInterface $entityManager, private PanierRepository $panierRepository) 
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
    if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
        return new RedirectResponse($targetPath);
    }

    $user = $token->getUser();
    $cartSession = $request->getSession()->get('panier');

    if ($cartSession) {
        foreach ($cartSession as $id => $quantity) {
            $product = $this->produitRepository->find($id);

            if (!$product) {
                continue;
            }

            $searchProductInUserCart = $this->panierRepository->findOneBy([
                'utilisateurs' => $user,
                'produit' => $product
            ]);

            if ($searchProductInUserCart) {
                $searchProductInUserCart->setQuantity($searchProductInUserCart->getQuantity() + $quantity);
            } else {
                $cart = new Panier();
                $cart->setUtilisateurs($user);
                $cart->setProduit($product);
                $cart->setQuantity($quantity);
                $this->entityManager->persist($cart);
            }
        }

        $this->entityManager->flush();
        $request->getSession()->remove('cart');
    }
        // For example:
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
