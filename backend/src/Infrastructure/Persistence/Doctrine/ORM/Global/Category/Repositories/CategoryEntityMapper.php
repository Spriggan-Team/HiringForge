<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\Repositories;

use App\Domain\Category\Category;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\CategoryEntity;


final class CategoryEntityMapper
{

    public function toDomain(
        CategoryEntity $entity
    ): Category
    {
        return Category::reconstitute(
            id: $entity->getId(),
            label: $entity->getLabel()
        );
    }


    public function toEntity(
        Category $category
    ): CategoryEntity
    {

        $entity = new CategoryEntity();

        $entity->setLabel(
            $category->label()
        );

        return $entity;
    }
}