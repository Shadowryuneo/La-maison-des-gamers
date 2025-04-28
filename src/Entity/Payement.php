<?php

namespace App\Entity;

use App\Repository\PayementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PayementRepository::class)]
class Payement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $numerodecommande = null;

    #[ORM\Column(length: 50)]
    private ?string $montant = null;

    #[ORM\Column(length: 10)]
    private ?string $datedecommande = null;

    #[ORM\Column(length: 150)]
    private ?string $modepayement = null;

    #[ORM\ManyToOne(inversedBy: 'Payement')]
    private ?Commande $commande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumerodecommande(): ?string
    {
        return $this->numerodecommande;
    }

    public function setNumerodecommande(string $numerodecommande): static
    {
        $this->numerodecommande = $numerodecommande;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDatedecommande(): ?string
    {
        return $this->datedecommande;
    }

    public function setDatedecommande(string $datedecommande): static
    {
        $this->datedecommande = $datedecommande;

        return $this;
    }

    public function getModepayement(): ?string
    {
        return $this->modepayement;
    }

    public function setModepayement(string $modepayement): static
    {
        $this->modepayement = $modepayement;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }
}
