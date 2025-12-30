<?php 


namespace App\Infrastructure\Persistence\MySQL\Repositories;

use DateTimeImmutable;

use Ramsey\Uuid\Uuid;
use Doctrine\ORM\EntityManagerInterface;

use App\Domain\Repositories\PostRepositioryInterface;
use App\Infrastructure\Persistence\MySQL\Doctrine\PostEntity;


class PostRepository implements PostRepositioryInterface {

    public function __construct(private EntityManagerInterface $em){}


    public function findAll(): array
    {
       $posts = $this->em->getRepository(PostEntity::class)->findAll();
       return $posts;
    }


    public function findOne(string $id): ?object
    {
        $entity = $this->em->getRepository(PostEntity::class)->find($id);
        return $entity;
    }


    public function create(string $title, array $content): void
    {
        $post  = new PostEntity();
        $today = new \DateTimeImmutable();

        $post->setId(Uuid::uuid4()->toString());
        $post->setTitle($title);
        $post->setContent($content);
        $post->setCreatedAt($today);
        $post->setUpdatedAt($today);

        $this->em->persist($post);
        $this->em->flush();
    }
}

?>