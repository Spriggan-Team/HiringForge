<?php

namespace App\Domain\Shared\Actor;

interface ActorRepositoryInterface
{
    public function findAll(): array;
}