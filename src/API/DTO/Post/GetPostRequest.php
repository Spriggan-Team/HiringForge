<?php

namespace App\Api\DTO\Post; 


use Symfony\Component\Validator\Constraints as Assert;


class GetPostRequest{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid,
    ){}
}


?>