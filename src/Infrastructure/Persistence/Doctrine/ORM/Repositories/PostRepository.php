<?php 


namespace App\Infrastructure\Persistence\Doctrine\ORM\Repositories;

use App\Domain\Entity\Post;
use App\Api\Exceptions\ApiRessourceNotFound;

use Doctrine\ORM\EntityManagerInterface;

use App\Domain\Repositories\PostRepositioryInterface;
use App\Domain\ValueObject\MergeRule;
use App\Infrastructure\Persistence\Doctrine\ORM\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Mapper\PostEntityMapper;
use App\Infrastructure\Persistence\Doctrine\ORM\PostEntity as ORMPostEntity;



class PostRepository implements PostRepositioryInterface {

    public function __construct(private EntityManagerInterface $manager){}


    public function getAll(string $accountId): array
    {
       $posts = $this->manager->getRepository(ORMPostEntity::class)->findBy([
        "account" => $accountId
       ]);
       for ($i=0; $i < count($posts); $i++) {
            $posts[$i] = PostEntityMapper::toDomainEntity($posts[$i]);
       }
       return $posts;
    }



    public function getById(string $accountId, string $postId): Post
    {
        $entity = $this->manager->getRepository(ORMPostEntity::class)->findOneBy([
            "id" => $postId,
            "account" => $accountId
        ]);
        if(!$entity){
           throw new ApiRessourceNotFound("Ressource not found"); 
        }
        return PostEntityMapper::toDomainEntity($entity);
    }


    public function save(Post $postDomain, string $accountId, MergeRule $rule = MergeRule::FULL_OVERWRITE  ) : void
    {
        $postEntity = $this->manager->getRepository(ORMPostEntity::class)->findOneBy([
            "account" => $accountId
        ]);

        if(!$postEntity){
            $post = PostEntityMapper::toDoctrineEntity($postDomain, $this->manager->find(AccountEntity::class, $accountId));
            $this->manager->persist($post);
        }
        else{
            PostEntityMapper::mergeIntoDoctrineEntity($postDomain, $postEntity,  $this->manager->find(AccountEntity::class, $accountId), $rule );
        }
        
        $this->manager->flush();
    }
}

?>