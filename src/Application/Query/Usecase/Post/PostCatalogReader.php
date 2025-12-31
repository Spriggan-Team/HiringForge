<?php

namespace App\Application\Query\Usecase\Post;

use App\Api\DTO\Post\PostResponse;
use App\Api\DTO\Post\GetPostCollectiontRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\PostRepository;

class PostCatalogReader
{

    public function __construct(private PostRepository $repository){}

    public function execute(GetPostCollectiontRequest $query): array
    {
        $data = $this->repository->getAll($query->accountId);

        for($i = 0; $i < count($data); $i++){
            $data[$i] = new PostResponse(
                id: $data[$i]->getId(),
                title: $data[$i]->getTitle(),
                content: $data[$i]->getContent(),
                createdAt: $data[$i]->getCreatedAt(),
                updatedAt: $data[$i]->getUpdatedAt(),
            );
        }
        
        return $data;
    }

}