<?php

namespace App\Entity;

use App\Repository\AdressefacturationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdressefacturationRepository::class)]
class Adressefacturation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codepostal = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Utilisateurs $utilisateurs = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCodepostal(): ?string
    {
        return $this->codepostal;
    }

    public function setCodepostal(?string $codepostal): static
    {
        $this->codepostal = $codepostal;

        return $this;
    }

    public function getUtilisateurs(): ?Utilisateurs
    {
        return $this->utilisateurs;
    }

    public function setUtilisateurs(?Utilisateurs $utilisateurs): static
    {
        $this->utilisateurs = $utilisateurs;

        return $this;
    }
}
