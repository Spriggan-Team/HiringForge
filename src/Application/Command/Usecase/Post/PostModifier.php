<?php

namespace App\Application\Command\Usecase\Post;

use App\Api\DTO\Post\MutatePostRequest;
use App\Domain\ValueObject\MergeRule;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\PostRepository;

class PostModifier
{
    public function __construct(private PostRepository $repository){}

    public function execute(MutatePostRequest $command):void
    {
        $post = $this->repository->getById($command->accountId, $command->uuid);
        if($command->title){
            $post->setTitle($command->title);
        }

        if($command->content){
            $post->setContent($command->content);
        }

        $this->repository->save($post, $command->uuid, MergeRule::PARTIAL_MERGE);
    }
}