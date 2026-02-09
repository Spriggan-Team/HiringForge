<?php

namespace App\Domain\Category;

class Category
{
    public function __construct(
        public string $name,
        public string $description,
    ){}
}