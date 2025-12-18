<?php 


namespace App\Infrastructure\Persistence\MySQL\Repository;


use Doctrine\ORM\EntityManagerInterface;

use App\Domain\Model\Post;
use App\Domain\Repositories\PostRepositioryInterface;
use App\Infrastructure\Persistence\MySQL\Doctrine\PostEntity;
use App\Infrastructure\Persistence\MySQL\Mapper\PostMapper;




class PostRepository implements PostRepositioryInterface{

    public function __construct(private EntityManagerInterface $em){}

    public function findAll(): array
    {
       $posts = $this->em->getRepository(PostEntity::class)->findAll();
       return $posts;
    }

    public function findOne(int $id): ?Post
    {
        $entity = $this->em->getRepository(PostEntity::class)->find($id);
        $post = $entity ? PostMapper::toDomain($entity) : $entity;
        return $post;
    }
}

?>