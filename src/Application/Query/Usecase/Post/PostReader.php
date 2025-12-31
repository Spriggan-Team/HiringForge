<?php

namespace App\Application\Query\Usecase\Post;

use App\Domain\Entity\Post;
use App\Api\DTO\Post\GetPostRequest;
use App\Api\DTO\Post\PostResponse;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\PostRepository;

class PostReader
{
    public function __construct(private PostRepository $repository){}

    public function execute(GetPostRequest $query): PostResponse
    {
        $post = $this->repository->getById($query->accountId, $query->uuid);
        $apiResponse =  new PostResponse(
                            id: $post->getId(),
                            title: $post->getTitle(),
                            content: $post->getContent(),
                            createdAt: $post->getCreatedAt(),
                            updatedAt: $post->getUpdatedAt()
                        );
        return $apiResponse;
    }
}