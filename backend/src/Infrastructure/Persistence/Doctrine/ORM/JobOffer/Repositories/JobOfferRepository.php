<?php 

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;


use App\Domain\Exception\RessourceNotFound;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\AccountId;
use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferRepositioryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\ORM\EntityManagerInterface;


class JobOfferRepository implements JobOfferRepositioryInterface
{

    public function __construct(private EntityManagerInterface $manager){}


    public function assertRelationWithUser(string $accountId, string $offerId): void
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @return JobOffer[]
     */
    public function findAll(string $accountId, string $offerId): array
    {
       $posts = $this->manager->getRepository(JobOfferEntity::class)->findBy([
        "account" => $accountId
       ]);
       for ($i=0; $i < count($posts); $i++) {
            $posts[$i] = JobOfferEntityMapper::toDomain($posts[$i]);
       }
       return $posts;
    }

    

    public function findById(string $accountId, string $jobOfferId): JobOffer
    {
        $entity = $this->manager->getRepository(JobOfferEntity::class)->findOneBy([
            "id" => $jobOfferId,
            "account" => $accountId
        ]);
        if(!$entity){
           throw new RessourceNotFound("Ressource not found"); 
        }
        return JobOfferEntityMapper::toDomain($entity);
    }

    public function fetchJobOfferViewCollection(?int $limit = null, ?int $skip = null): array
    {
        throw new \Exception('Not implemented');
    }


    public function save(JobOffer $offer, AccountId $userId): void
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

    public function change(JobOffer $jobOffer, string $offerId, string $accountId): void
    {
        throw new \Exception('Not implemented');
    }


    public function publish(string $offerId, string $userId): void
    {
        throw new \Exception('Not implemented');
    }

    public function associateImagesWithJob(string $offerId, array $images): void
    {
        throw new \Exception('Not implemented');
    }


    public function delete(string $userId, string $uuid): void
    {
        $entity = $this->manager->find(JobOfferEntity::class, $uuid);
        if(!$entity)
            throw new RessourceNotFound();
        
        $this->manager->remove($entity);
        $this->manager->flush();
    }

    public function removeImageFromJob(string $offerId, string $fileName): void
    {
        throw new \Exception('Not implemented');
    }
}

?>