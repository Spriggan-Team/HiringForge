<?php

namespace App\Domain\Department;

interface DepartmentRepositoryInterface{
    public function  get(int $id): Department;
}