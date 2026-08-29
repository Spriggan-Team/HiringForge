<?php

namespace App\Domain\Candidate;

use App\Domain\Shared\Address;
use App\Domain\Shared\Account\AccountLightModel;

readonly class CandidateLightModel extends AccountLightModel
{
    public function __construct(
        string $id,
        string $firstName,
        string $lastName,
        string $email,
        array $address,
        ?string $imageId = null,
        public ?float $delayInSec =null,
        public ?string $headline = null,
    ) {
        parent::__construct(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            imageId: $imageId,
            address: $address
        );
    }
}