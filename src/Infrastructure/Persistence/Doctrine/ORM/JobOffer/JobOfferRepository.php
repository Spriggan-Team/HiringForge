<?php 

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;


use App\Domain\Exception\RessourceNotFound;

use App\Domain\JobOffer\JobOffer;
use App\Domain\User\UserId;

use App\Domain\Repositories\JobOfferRepositioryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\ORM\EntityManagerInterface;


class JobOfferRepository implements JobOfferRepositioryInterface
{

    public function __construct(private EntityManagerInterface $manager){}


    public function getAll(string $accountId): array
    {
       $posts = $this->manager->getRepository(JobOfferEntity::class)->findBy([
        "account" => $accountId
       ]);
       for ($i=0; $i < count($posts); $i++) {
            $posts[$i] = JobOfferEntityMapper::toDomain($posts[$i]);
       }
       return $posts;
    }



    public function getById(string $userId, string $jobOfferId): JobOffer
    {
        $entity = $this->manager->getRepository(JobOfferEntity::class)->findOneBy([
            "id" => $jobOfferId,
            "account" => $userId
        ]);
        if(!$entity){
           throw new RessourceNotFound("Ressource not found"); 
        }
        return JobOfferEntityMapper::toDomain($entity);
    }


    public function save(JobOffer $offer, UserId $userId): void
    {
        $entity = $this->manager->find(JobOffer::class, $offer->id());

        if (!$entity) {
            $user = $this->manager->getReference(UserEntity::class, $userId->value());
            $entity = JobOfferEntityMapper::toDoctrine($offer, $user);
            $this->manager->persist($entity);
        }
        else {
            JobOfferEntityMapper::copy($offer, $entity);
        }
        $this->manager->flush();
    }



    public function delete(string $userId, string $uuid): void
    {
        $entity = $this->manager->find(JobOfferEntity::class, $uuid);
        if(!$entity)
            throw new RessourceNotFound();
        
        $this->manager->remove($entity);
        $this->manager->flush();
    }
}

?>