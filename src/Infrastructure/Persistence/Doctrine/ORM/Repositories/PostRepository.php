<?php 


namespace App\Infrastructure\Persistence\Doctrine\ORM\Repositories;

use App\Api\Exceptions\ApiRessourceNotFound;
use App\Domain\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;

use App\Domain\Repositories\JobOfferRepositioryInterface;
use App\Domain\ValueObject\MergeRule;

use App\Infrastructure\Persistence\Doctrine\ORM\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Mapper\JobOfferEntityMapper;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity as ORMEntity;



class JobOfferRepository implements JobOfferRepositioryInterface {

    public function __construct(private EntityManagerInterface $manager){}


    public function getAll(string $accountId): array
    {
       $posts = $this->manager->getRepository(ORMEntity::class)->findBy([
        "account" => $accountId
       ]);
       for ($i=0; $i < count($posts); $i++) {
            $posts[$i] = JobOfferEntityMapper::toDomainEntity($posts[$i]);
       }
       return $posts;
    }



    public function getById(string $accountId, string $postId): JobOffer
    {
        $entity = $this->manager->getRepository(ORMEntity::class)->findOneBy([
            "id" => $postId,
            "account" => $accountId
        ]);
        if(!$entity){
           throw new ApiRessourceNotFound("Ressource not found"); 
        }
        return JobOfferEntityMapper::toDomainEntity($entity);
    }


    public function save(JobOffer $postDomain, string $accountId, MergeRule $rule = MergeRule::FULL_OVERWRITE  ) : void
    {
        $postEntity = $this->manager->getRepository(ORMEntity::class)->findOneBy([
            "account" => $accountId
        ]);

        if(!$postEntity){
            $post = JobOfferEntityMapper::toDoctrineEntity($postDomain, $this->manager->find(AccountEntity::class, $accountId));
            $this->manager->persist($post);
        }
        else{
            JobOfferEntityMapper::mergeIntoDoctrineEntity($postDomain, $postEntity,  $this->manager->find(AccountEntity::class, $accountId), $rule );
        }
        
        $this->manager->flush();
    }


    public function delete(string $accountId, string $uuid): void
    {
        $entity = $this->manager->find(ORMEntity::class, $uuid);
        if(!$entity)
            throw new ApiRessourceNotFound();
        
        $this->manager->remove($entity);
        $this->manager->flush();
    }
}

?>