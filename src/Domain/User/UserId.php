<?php


namespace App\Domain\User;


use DomainException;


class UserId
{
    private function __construct(private string $id)
    {
        if(!self::isValid($id)){
            throw new DomainException("Invalid User Id");
        }
        $this->id = $id;
    }

    public static function fromString(string $id): static
    {
        return new self($id);
    }

    public static function isValid(string $id)
    {
        return \Ramsey\Uuid\Uuid::isValid($id);
    }

    public function value(): string
    {
        return $this->id;
    }
}