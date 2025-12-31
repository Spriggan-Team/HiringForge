<?php

namespace App\Application\Command\Usecase\Post;

use App\Api\DTO\Post\CreatePostRequest;
use App\Domain\Entity\Post;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\PostRepository;
use Ramsey\Uuid\Uuid;

class PostRecorder
{
    public function __construct(private PostRepository $repository){}

    public function execute(CreatePostRequest $command): void
    {
        $today = new  \DateTimeImmutable();
        
        $post = new Post(
            id: Uuid::uuid4(),
            title: $command->title,
            content: $command->content,
            createdAt: $today,
            updatedAt: $today
        );
        
        $this->repository->save($post, $command->accountId);
    }
}

?>