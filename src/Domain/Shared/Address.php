<?php

namespace App\Domain\Shared;

final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country
    ) {
        if ($postalCode === '') {
            throw new \InvalidArgumentException('Postal code required');
        }
    }
    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return "{$this->street}, {$this->postalCode} {$this->city}, {$this->country}";
    }

}
