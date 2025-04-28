<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixtotal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?Utilisateurs $utilisateurs = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresselivraisoncommande $Adresselivraisoncommande = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adressefacturationcommande $Adressefacturationcommande = null;

    /**
     * @var Collection<int, Payement>
     */
    #[ORM\OneToMany(targetEntity: Payement::class, mappedBy: 'commande')]
    private Collection $Payement;

    /**
     * @var Collection<int, Detailcommande>
     */
    #[ORM\OneToMany(targetEntity: Detailcommande::class, mappedBy: 'commande')]
    private Collection $Detailcommande;

    public function __construct()
    {
        $this->Payement = new ArrayCollection();
        $this->Detailcommande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrixtotal(): ?float
    {
        return $this->prixtotal;
    }

    public function setPrixtotal(?float $prixtotal): static
    {
        $this->prixtotal = $prixtotal;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

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

    public function getAdresselivraisoncommande(): ?Adresselivraisoncommande
    {
        return $this->Adresselivraisoncommande;
    }

    public function setAdresselivraisoncommande(?Adresselivraisoncommande $Adresselivraisoncommande): static
    {
        $this->Adresselivraisoncommande = $Adresselivraisoncommande;

        return $this;
    }

    public function getAdressefacturationcommande(): ?Adressefacturationcommande
    {
        return $this->Adressefacturationcommande;
    }

    public function setAdressefacturationcommande(?Adressefacturationcommande $Adressefacturationcommande): static
    {
        $this->Adressefacturationcommande = $Adressefacturationcommande;

        return $this;
    }

    /**
     * @return Collection<int, Payement>
     */
    public function getPayement(): Collection
    {
        return $this->Payement;
    }

    public function addPayement(Payement $payement): static
    {
        if (!$this->Payement->contains($payement)) {
            $this->Payement->add($payement);
            $payement->setCommande($this);
        }

        return $this;
    }

    public function removePayement(Payement $payement): static
    {
        if ($this->Payement->removeElement($payement)) {
            // set the owning side to null (unless already changed)
            if ($payement->getCommande() === $this) {
                $payement->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Detailcommande>
     */
    public function getDetailcommande(): Collection
    {
        return $this->Detailcommande;
    }

    public function addDetailcommande(Detailcommande $detailcommande): static
    {
        if (!$this->Detailcommande->contains($detailcommande)) {
            $this->Detailcommande->add($detailcommande);
            $detailcommande->setCommande($this);
        }

        return $this;
    }

    public function removeDetailcommande(Detailcommande $detailcommande): static
    {
        if ($this->Detailcommande->removeElement($detailcommande)) {
            // set the owning side to null (unless already changed)
            if ($detailcommande->getCommande() === $this) {
                $detailcommande->setCommande(null);
            }
        }

        return $this;
    }
}
