<?php

namespace App\Application\Command\Usecase\Post;

use App\Api\DTO\Post\DeletePostRequest;
use App\Api\Exceptions\ApiRessourceNotFound;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\PostRepository;

class PostEraser
{
    public function __construct(private PostRepository $repository){}

    public function execute(DeletePostRequest $command): void
    {
        $this->repository->delete($command->accountId, $command->uuid );
    }
}