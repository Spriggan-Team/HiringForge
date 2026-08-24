<?php

namespace App\Domain\File;

/**
 * Poorts for profil management
 */
interface  FileRepositoryInterface
{
    public function save(Media $file): int;
    public function delete(int $fileId);
}