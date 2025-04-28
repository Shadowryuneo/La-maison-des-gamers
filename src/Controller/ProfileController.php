<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
// use Symfony\Component\Security\Core\User\UserInterface;
use symfony\component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



#[Route('/compte')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', []);
    }

    #[Route('/modifier', name: 'app_profile_edit', methods:['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            $this->addFlash('success', 'Votre compte a bien été modifier');
            return $this->redirectToRoute('app_profile_index');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/mot-de-passe/modifier', name: 'app_profile_password_edit', methods: ['GET', 'POST'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \LogicException('Aucun utilisateur connecté.');
        }

        if ($user instanceof Utilisateurs) {

            $form = $this->createFormBuilder()
                ->add('old_password', PasswordType::class, [
                    'label' => 'Ancien mot de passe :',
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer votre ancien mot de passe']),
                        new UserPassword(['message' => 'Ancien mot de passe incorrect']),
                    ],
                ])
                ->add('new_password', PasswordType::class, [
                    'label' => 'Nouveau mot de passe :',
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer un nouveau mot de passe']),
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Le mot de passe doit contenir au moins 12 caractères',
                        ]),
                    ],
                ])
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newPassword = $form->get('new_password')->getData();

                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
                return $this->redirectToRoute('app_profile_index');
            }


            return $this->render('profile/editPassword.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('app_profile_index');
    }

    #[Route('/supprimer', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(RequestStack $requestStack, EntityManagerInterface $em, Security $security): Response
    {
    // Récupération de l'utilisateur connecté
    $user = $security->getUser();

    if (!$user instanceof PasswordAuthenticatedUserInterface) {
        throw new \LogicException('Aucun utilisateur connecté.');
    }

    // Suppression de l'utilisateur en base de données
    $em->remove($user);
    $em->flush();

    // Invalidation de la session via RequestStack
    $session = $requestStack->getSession();
    $session->invalidate();

    // Suppression du token d'authentification
    $this->container->get('security.token_storage')->setToken(null);

    // Redirection vers la page d'accueil après suppression
    return $this->redirectToRoute('app_home');
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
