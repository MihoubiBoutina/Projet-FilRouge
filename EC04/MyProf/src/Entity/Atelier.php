<?php

namespace App\Entity;

use App\Repository\AtelierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AtelierRepository::class)]
class Atelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    private ?int $dureeHeure = null;

    #[ORM\Column]
    private ?int $place = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'ateliers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserFormateur $formateur = null;

    /**
     * @var Collection<int, InscriptionAtelier>
     */
    #[ORM\OneToMany(targetEntity: InscriptionAtelier::class, mappedBy: 'atelier', orphanRemoval: true)]
    private Collection $inscriptionAteliers;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'atelier', orphanRemoval: true)]
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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getDureeHeure(): ?int
    {
        return $this->dureeHeure;
    }

    public function setDureeHeure(int $dureeHeure): static
    {
        $this->dureeHeure = $dureeHeure;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(int $place): static
    {
        $this->place = $place;

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

    public function getFormateur(): ?UserFormateur
    {
        return $this->formateur;
    }

    public function setFormateur(?UserFormateur $formateur): static
    {
        $this->formateur = $formateur;

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
            $inscriptionAtelier->setAtelier($this);
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
            $avi->setAtelier($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getAtelier() === $this) {
                $avi->setAtelier(null);
            }
        }

        return $this;
    }

}
