<?php

namespace App\Application\Query\Handlers\User;


/**
 * Represents a lightweight user profile projection.
 *
 * Contains only the essential information required
 * to display or manage a user within listing or
 * controlled access contexts.
 *
 * This is not a full domain entity but a read model
 * optimized for retrieval and presentation.
 */
class UserProfileItem
{
     public function __construct(
        /** */
        public string $name,

         /** */
        public string $email,

         /** */
        public string $siret,

        public array $address,

        public array $images = [],

        public ?string $presentation =null,

        public array $job_offers = [],
     ){}
}