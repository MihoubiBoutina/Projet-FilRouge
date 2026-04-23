<?php
namespace App\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class LogVisite
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'int')]
    private int $atelierId;

    #[MongoDB\Field(type: 'string')]
    private ?string $visiteurIp = null;

    #[MongoDB\Field(type: 'int')]
    private ?int $userId = null; // Optionnel : l'ID de l'utilisateur s'il est connecté

    #[MongoDB\Field(type: 'date')]
    private \DateTime $visitedAt;

    public function __construct()
    {
        $this->visitedAt = new \DateTime();
    }

    public function getId(): string 
    { 
        return $this->id; 
    }

    public function getAtelierId(): int 
    { 
        return $this->atelierId; 
    }

    public function setAtelierId(int $atelierId): self 
    { 
        $this->atelierId = $atelierId; 
        return $this; 
    }

    public function getVisiteurIp(): ?string 
    { 
        return $this->visiteurIp; 
    }

    public function setVisiteurIp(?string $visiteurIp): self 
    { 
        $this->visiteurIp = $visiteurIp; 
        return $this; 
    }

    public function getUserId(): ?int 
    { 
        return $this->userId; 
    }

    public function setUserId(?int $userId): self 
    { 
        $this->userId = $userId; 
        return $this; 
    }

    public function getVisitedAt(): \DateTime 
    { 
        return $this->visitedAt; 
    }

    public function setVisitedAt(\DateTime $visitedAt): self 
    { 
        $this->visitedAt = $visitedAt; 
        return $this; 
    }
}
