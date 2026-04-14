<?php
namespace App\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Avis
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private string $commentaire;

    #[MongoDB\Field(type: 'int')]
    private $apprenantId;

    #[MongoDB\Field(type: 'int')]
    private int $note;

    #[MongoDB\Field(type: 'int')]
    private int $atelierId; // On stocke l'ID MySQL de l'atelier

    #[MongoDB\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    // Ajoutez vos Getters et Setters (ou générez-les)
    public function getId(): string 
    { 
        return $this->id; 
    }

    public function getCommentaire(): string 
    { 
        return $this->commentaire; 
    }

    public function setCommentaire(string $c): self 
    { 
        $this->commentaire = $c; 
        return $this; 
    }

    public function getApprenantId() 
    { 
        return $this->apprenantId; 
    }

    public function setApprenantId(int $apprenantId): self
    {
        $this->apprenantId = $apprenantId;
        return $this;
    }

    public function getNote(): int 
    { 
        return $this->note; 
    }
    public function setNote(int $n): self 
    { 
        $this->note = $n; return $this; 
    }
    public function getAtelierId(): int 
    { 
        return $this->atelierId; 
    }
    public function setAtelierId(int $id): self 
    { 
        $this->atelierId = $id; return $this; 
    }

    public function getCreatedAt()
    { 
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
