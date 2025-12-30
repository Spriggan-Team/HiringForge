<?php

namespace App\Api\DTO\Post;

use Symfony\Component\Validator\Constraints as Assert;


final class CreatePostDTO
{

    #[Assert\NotBlank]
    #[Assert\Type]
    public string $title;

    #[Assert\Count]
    public array $content = [];

}