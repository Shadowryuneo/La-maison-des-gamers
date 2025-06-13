<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;


class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function envoyerConfirmationCommande(string $to, string $subject, array $context)
    {
        $html = $this->twig->render('commande/emails/confirmation_Commande.html.twig', $context);

        $email = (new Email())
        ->from('noreply@votresite.com')
        ->to($to)
        ->subject($subject)
        ->html($html);

        $this->mailer->send($email);
    }
}