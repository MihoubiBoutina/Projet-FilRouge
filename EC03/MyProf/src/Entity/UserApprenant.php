<?php

namespace App\Entity;

use App\Repository\UserApprenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserApprenantRepository::class)]
class UserApprenant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastSessionId = null;


    #[ORM\Column(length: 50, nullable: true)]
private ?string $niveau = null;

#[ORM\Column(type: 'text', nullable: true)]
private ?string $objectif = null;

#[ORM\Column(length: 50, nullable: true)]
private ?string $disponibilite = null;

#[ORM\Column(options: ['default' => false])]
private bool $profilComplet = false;


    /**
     * @var Collection<int, InscriptionAtelier>
     */
    #[ORM\OneToMany(targetEntity: InscriptionAtelier::class, mappedBy: 'apprenant', orphanRemoval: true)]
    private Collection $inscriptionAteliers;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'apprenant', orphanRemoval: true)]
    private Collection $avis;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->inscriptionAteliers = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getLastSessionId(): ?string
    {
        return $this->lastSessionId;
    }

    public function setLastSessionId(?string $lastSessionId): static
    {
        $this->lastSessionId = $lastSessionId;

        return $this;
    }

    /**
     * @return Collection<int, InscriptionAtelier>
     */
    public function getInscriptionAteliers(): Collection
    {
        return $this->inscriptionAteliers;
    }

    public function addInscriptionAtelier(InscriptionAtelier $inscriptionAtelier): static
    {
        if (!$this->inscriptionAteliers->contains($inscriptionAtelier)) {
            $this->inscriptionAteliers->add($inscriptionAtelier);
            $inscriptionAtelier->setApprenant($this);
        }

        return $this;
    }

    public function removeInscriptionAtelier(InscriptionAtelier $inscriptionAtelier): static
        {
            $this->inscriptionAteliers->removeElement($inscriptionAtelier);
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
            $avi->setApprenant($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getApprenant() === $this) {
                $avi->setApprenant(null);
            }
        }

        return $this;
    }


    public function getNiveau(): ?string
{
    return $this->niveau;
}

public function setNiveau(?string $niveau): static
{
    $this->niveau = $niveau;
    return $this;
}

public function getObjectif(): ?string
{
    return $this->objectif;
}

public function setObjectif(?string $objectif): static
{
    $this->objectif = $objectif;
    return $this;
}

public function getDisponibilite(): ?string
{
    return $this->disponibilite;
}

public function setDisponibilite(?string $disponibilite): static
{
    $this->disponibilite = $disponibilite;
    return $this;
}

public function isProfilComplet(): bool
{
    return $this->profilComplet;
}

public function setProfilComplet(bool $profilComplet): static
{
    $this->profilComplet = $profilComplet;
    return $this;
}

}
