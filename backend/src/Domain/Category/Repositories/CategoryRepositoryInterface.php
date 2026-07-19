<?php

namespace App\Domain\Category\Repositories;

use App\Domain\Category\Category;

interface CategoryRepositoryInterface
{
    /**
     * @param string[] categoriesIds - an array of the category to check existence
     * @throws \DomainException|\Exception  - logical error or error thrown by the systm
     * @return Category[]
     */
    public function getExistingByIds(array $categoriesIds): array;


}