<?php

namespace App\Infrastructure\Persistence\MySQL\Mapper;

use App\Domain\Model\Post;
use App\Infrastructure\Persistence\MySQL\Doctrine\PostEntity;
use DateTime;



class PostMapper{

    public static function toDomain(PostEntity $entity): Post
    {
        return new Post(
            $entity->getId(),
            $entity->getTitle(),
            $entity->getContent(),
            $entity->getCreatedAt(),
            $entity->getUpdatedAt()
        );
    }
}