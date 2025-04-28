<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Categories $categorie = null;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'produit')]
    private Collection $avis;

    #[ORM\Column]
    private ?bool $status = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $conssoles = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jeuxvideo = null;

    /**
     * @var Collection<int, Panier>
     */
    #[ORM\OneToMany(targetEntity: Panier::class, mappedBy: 'produit')]
    private Collection $panier;

    /**
     * @var Collection<int, Detailcommande>
     */
    #[ORM\OneToMany(targetEntity: Detailcommande::class, mappedBy: 'produit')]
    private Collection $detailcommande;

    #[ORM\Column(nullable: true)]
    private ?int $stock = 0;

    public function __construct()
    {
        $this->avis = new ArrayCollection();
        $this->panier = new ArrayCollection();
        $this->detailcommande = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCategorie(): ?Categories
    {
        return $this->categorie;
    }

    public function setCategorie(?Categories $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setProduit($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getProduit() === $this) {
                $avi->setProduit(null);
            }
        }

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getConssoles(): ?string
    {
        return $this->conssoles;
    }

    public function setConssoles(string $conssoles): static
    {
        $this->conssoles = $conssoles;

        return $this;
    }

    public function getJeuxvideo(): ?string
    {
        return $this->jeuxvideo;
    }

    public function setJeuxvideo(string $jeuxvideo): static
    {
        $this->jeuxvideo = $jeuxvideo;

        return $this;
    }

    /**
     * @return Collection<int, Panier>
     */
    public function getPanier(): Collection
    {
        return $this->panier;
    }

    public function addPanier(Panier $panier): static
    {
        if (!$this->panier->contains($panier)) {
            $this->panier->add($panier);
            $panier->setProduit($this);
        }

        return $this;
    }

    public function removePanier(Panier $panier): static
    {
        if ($this->panier->removeElement($panier)) {
            // set the owning side to null (unless already changed)
            if ($panier->getProduit() === $this) {
                $panier->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Detailcommande>
     */
    public function getDetailcommande(): Collection
    {
        return $this->detailcommande;
    }

    public function addDetailcommande(Detailcommande $detailcommande): static
    {
        if (!$this->detailcommande->contains($detailcommande)) {
            $this->detailcommande->add($detailcommande);
            $detailcommande->setProduit($this);
        }

        return $this;
    }

    public function removeDetailcommande(Detailcommande $detailcommande): static
    {
        if ($this->detailcommande->removeElement($detailcommande)) {
            // set the owning side to null (unless already changed)
            if ($detailcommande->getProduit() === $this) {
                $detailcommande->setProduit(null);
            }
        }

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }
}
