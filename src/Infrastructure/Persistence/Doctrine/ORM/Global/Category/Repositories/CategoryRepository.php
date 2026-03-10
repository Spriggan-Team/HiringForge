<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\Repositories;

use App\Domain\Category\Repositories\CategoryRepositoryInterace;

class CategoryRepository implements CategoryRepositoryInterace
{
    public function asserCategoriesExistence(array $categoriesIds): void
    {
        throw new \Exception('Not implemented');
    }
}