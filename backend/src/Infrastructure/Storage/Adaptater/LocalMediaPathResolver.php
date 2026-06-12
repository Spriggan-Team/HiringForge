<?php

namespace App\Infrastructure\Storage\Adaptater;

class LocalMediaPathResolver // implements MediaPathResolverInterface
{
    public function resolveDirectory(?string $ownerType, ?string $mediaPurpose): string
    {
        if ($ownerType === 'COMPANY' && $mediaPurpose === 'PROFILE') {
            return 'uploads/companies/logos';
        }

        if ($ownerType === 'USER' && $mediaPurpose === 'PROFILE') {
            return 'uploads/users/profiles';
        }

        // Si c'est null ou inconnu, TOUT LE MONDE (lecture et écriture) va ici
        return 'uploads/defaults'; 
    }
}