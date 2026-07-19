<?php

namespace App\Application\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;

final class SalaryRequest
{
    public function __construct(

        /**
         * Minimum annual salary
         */
        #[Assert\PositiveOrZero]
        public ?float $min = null,


        /**
         * Maximum annual salary
         */
        #[Assert\PositiveOrZero]
        public ?float $max = null,


        /**
         * ISO 4217 currency code (EUR, USD, GBP...)
         */
        #[Assert\Currency]
        public string $currency = "EUR",

    ) {
        if (
            $this->min !== null &&
            $this->max !== null &&
            $this->min > $this->max
        ) {
            throw new \InvalidArgumentException(
                "Minimum salary cannot be greater than maximum salary"
            );
        }
    }
}