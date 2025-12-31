<?php

namespace App\Domain\Repositories;

use App\Domain\Entity\Post;
use App\Domain\ValueObject\MergeRule;

interface PostRepositioryInterface
{
    /**
     * @return array<Post>
     * return a collection of all the post stored in bdd
     */
    public function getAll(string $accountId): array;
    
    /**
     * @return Post
     * @throws ApiRessourceNotFound
     * seachr for an existing post in the bdd an return it
     */
    public function getById(string $id, string $postId): ?Post;
    
    /**
     * @return void
     * save the post in bdd
     * adaptated for post, patch, put Htpp request
     */
    public function save(Post $post, string $accountId,  MergeRule $rule = MergeRule::FULL_OVERWRITE ): void;
}