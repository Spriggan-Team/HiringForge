<?php

namespace App\Domain\File;

abstract class Media
{
    public function __construct(
        public string $name,        // the name (a uniq generated one)
        public float  $size,        //stored in bytes
        public string $mime,        // th mime type of the file 
        public ?string $originalName = null,
        public ?int $id = null,
        public ?\DateTimeImmutable $createdAt = null,
    ){}

}