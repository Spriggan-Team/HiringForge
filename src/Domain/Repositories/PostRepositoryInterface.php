<?php

namespace App\Domain\Repositories;

use App\Domain\Model\PostData;

interface PostRepositioryInterface
{
    public function findAll(): array;
    
    public function findOne(string $id): ?object;
    
    public function create(string $title, array $content): void;
}