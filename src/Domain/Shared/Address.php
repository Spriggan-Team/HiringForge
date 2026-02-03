<?php

namespace App\Domain\Shared;

/**
 * This is used to represent an address
 * Its only pupose shoul be to represent an address.
 * This classe is used later as a readonly for json parsing goals.
 */
final class Address
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country,
    ) {
        if ($postalCode === '') {
            throw new \InvalidArgumentException('Postal code required');
        }
    }

    // public function __toString()
    // {
    //     return $this->toString();
    // }

    // public function toString(): string
    // {
    //     return "{$this->street}, {$this->postalCode} {$this->city}, {$this->country}";
    // }

}
