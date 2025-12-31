<?php

namespace App\Api\DTO\Post;

use Symfony\Component\Validator\Constraints as Assert;

class GetPostCollectiontRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $accountId
    ){}
}