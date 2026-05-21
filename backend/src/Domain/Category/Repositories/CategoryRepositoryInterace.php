<?php

namespace App\Domain\Category\Repositories;


interface CategoryRepositoryInterace
{
    /**
     * @param string[] categoriesIds - an array of the category to check existence
     * @throws \DomainException|\Exception  - logical error or error thrown by the systm
     * @return void
     */
    public function asserCategoriesExistence(array $categoriesIds): void;
}