<?php

namespace App\Application\Query\Account;


/**
 * Represents a lightweight account's profile projection.
 *
 * Contains only the essential information required
 * to display or manage a user within listing or
 * controlled access contexts.
 *
 * This is not a full domain entity but a read model
 * optimized for retrieval and presentation.
 */
class AccountProfile
{
   public function __construct(
      /** */
      public string $lastname,

      /** */
      public string $firstname,

      /** */
      public string $email,

      public string $image,
   ){}
}