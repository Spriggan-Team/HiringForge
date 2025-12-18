<?php 


namespace App\Usecases\Post;

use App\Domain\Model\Post;

// Read post


interface PostReader
{

    /**
     * @return object[]
     */
    public function findAll(): array;

    public function findOne(int $id): Post | null;
}