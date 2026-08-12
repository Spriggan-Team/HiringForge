<?php

namespace App\Domain\Shared;

enum RootPath: string
{
    /** * Resolve the storage path from the project root. * * Example: * /project/backend/private/Vault/... */
    case PROJECT = "project";

    /** * Use the storage path directly as an absolute/system path. * * Example: * /var/storage/private/Vault/... */
    case SYSTEM = "system";

    /** * Path relative to the public directory. * * uploads/... */
    case PUBLIC = 'public';
}