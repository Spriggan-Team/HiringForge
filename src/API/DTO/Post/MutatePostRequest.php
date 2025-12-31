<?php

namespace App\Api\DTO\Post;


use Symfony\Component\Validator\Constraints as Assert;

class MutatePostRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $accountId,

        #[Assert\Uuid]
        public string $uuid,

        public ?string $title = null,
        public ?array $content = null,
    ){}
}