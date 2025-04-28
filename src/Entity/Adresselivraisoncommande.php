<?php

namespace App\Entity;

use App\Repository\AdresselivraisoncommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdresselivraisoncommandeRepository::class)]
class Adresselivraisoncommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
