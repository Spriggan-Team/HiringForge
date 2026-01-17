<?php 


namespace App\Infrastructure\Persistence\Doctrine\ORM\Repositories;


use App\Api\Exceptions\ApiRessourceNotFound;

use App\Domain\JobOffer\JobOffer;
use Doctrine\ORM\EntityManagerInterface;


use App\Domain\User\UserId;
use App\Domain\Repositories\JobOfferRepositioryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Mappers\JobOfferEntityMapper as JobOfferMapper;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity as ORMEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity;

class JobOfferRepository implements JobOfferRepositioryInterface
{

    public function __construct(private EntityManagerInterface $manager){}


    public function getAll(string $accountId): array
    {
       $posts = $this->manager->getRepository(ORMEntity::class)->findBy([
        "account" => $accountId
       ]);
       for ($i=0; $i < count($posts); $i++) {
            $posts[$i] = JobOfferMapper::toDomain($posts[$i]);
       }
       return $posts;
    }



    public function getById(string $userId, string $jobOfferId): JobOffer
    {
        $entity = $this->manager->getRepository(ORMEntity::class)->findOneBy([
            "id" => $jobOfferId,
            "account" => $userId
        ]);
        if(!$entity){
           throw new ApiRessourceNotFound("Ressource not found"); 
        }
        return JobOfferMapper::toDomain($entity);
    }


    public function save(JobOffer $offer, UserId $userId): void
    {
        $entity = $this->manager->find(ORMEntity::class, $offer->id());

        if (!$entity) {
            $user = $this->manager->getReference(UserEntity::class, $userId->value());
            $entity = JobOfferMapper::toDoctrine($offer, $user);
            $this->manager->persist($entity);
        }
        else {
            JobOfferMapper::copy($offer, $entity);
        }
        $this->manager->flush();
    }



    public function delete(string $userId, string $uuid): void
    {
        $entity = $this->manager->find(ORMEntity::class, $uuid);
        if(!$entity)
            throw new ApiRessourceNotFound();
        
        $this->manager->remove($entity);
        $this->manager->flush();
    }
}

?>