<?php

namespace App\Domain\Repositories;

use App\Domain\Model\Post;


interface PostRepositioryInterface
{
    public function findAll(): array;
    public function findOne(int $id): ?Post;
}