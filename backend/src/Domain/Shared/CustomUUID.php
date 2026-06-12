<?php


namespace App\Domain\Shared;


class CustomUUID{
    public static function generate(): string {
       return \Ramsey\Uuid\Uuid::uuid4();
    }

    public static function isValid(string $id): bool{
        return \Ramsey\Uuid\Uuid::isValid($id);
    }
}