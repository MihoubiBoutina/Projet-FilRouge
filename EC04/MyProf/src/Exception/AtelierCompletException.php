<?php

declare(strict_types=1);

namespace App\Exception;

final class AtelierCompletException extends \DomainException
{
    public function __construct(?int $atelierId = null)
    {
        $suffix = $atelierId === null ? '' : sprintf(' (atelier %d)', $atelierId);

        parent::__construct('Cet atelier est complet : aucune inscription supplémentaire ne peut être ajoutée.' . $suffix);
    }
}
