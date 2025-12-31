<?php

namespace App\Api\DTO\Post;


use Symfony\Component\Validator\Constraints as Assert;


class DeletePostRequest
{
    public function __construct(
        #[Assert\Uuid]
        #[Assert\NotNull]
        public string $uuid,

        #[Assert\Uuid]
        #[Assert\NotNull]
        public string $accountId,
    ){}
}

