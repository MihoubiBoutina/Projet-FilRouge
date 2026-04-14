<?php

namespace App\Entity;

use App\Repository\InscriptionAtelierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionAtelierRepository::class)]
class InscriptionAtelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $registeredAt = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionAteliers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserApprenant $apprenant = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionAteliers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Atelier $atelier = null;

    public function __construct()
        {
            $this->registeredAt = new \DateTimeImmutable();
            $this->status = 'CONFIRMED';
        }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeImmutable $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getApprenant(): ?UserApprenant
    {
        return $this->apprenant;
    }

    public function setApprenant(UserApprenant $apprenant): static
    {
        $this->apprenant = $apprenant;

        return $this;
    }

    public function getAtelier(): ?Atelier
    {
        return $this->atelier;
    }

    public function setAtelier(Atelier $atelier): static
    {
        $this->atelier = $atelier;

        return $this;
    }
}
