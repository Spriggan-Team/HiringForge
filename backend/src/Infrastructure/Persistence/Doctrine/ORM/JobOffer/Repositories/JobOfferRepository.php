<?php 

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\JobOffer\JobOffer;
use App\Domain\Shared\Account\AccountId;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\JobOffer\JobOfferImage;
use App\Domain\JobOffer\JobOfferRepositoryInterface;

use App\Domain\JobOffer\JobPublicationStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\ORM\EntityManagerInterface;
use Override;



class JobOfferRepository implements JobOfferRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $manager,
        private JobOfferEntityMapper $mapper
    ){}


    public function assertRelationWithUser(string $accountId, string $offerId): void
    {
        $count = (int) $this->manager->createQueryBuilder()
            ->select('COUNT(j.id)')
            ->from(JobOfferEntity::class, 'j')
            ->where('j.id = :offerId')
            ->andWhere('j.user = :accountId') 
            ->setParameter('offerId', $offerId)
            ->setParameter('accountId', $accountId)
            ->getQuery()
            ->getSingleScalarResult();

        if ($count === 0) {
            throw new \DomainException(sprintf(
                'L\'offre d\'emploi ID "%s" n\'existe pas ou n\'appartient pas à l\'utilisateur ID "%s".',
                $offerId,
                $accountId
            ));
        }
    }


    /**
     * @param string $offerId
     * @param JobOfferImage[] $images
     */
    #[Override]
    public function associateImagesWithJob(string $offerId, array $images): void
    {
        if (empty($images)) {
            return;
        }

        $repository = $this->manager->getRepository(JobOfferEntity::class);
        /** @var  JobOfferEntity | null*/
        $jobOffer = $repository->find($offerId);

        if (!$jobOffer) {
            throw new \DomainException(sprintf('L\'offre d\'emploi ID "%s" n\'existe pas.', $offerId));
        }

        foreach ($images as $image) {
            $file =  FileEntity::create(
                        name: $image->media->name,
                        mime: $image->media->mime,
                        size: $image->media->size
                    );
            $jobOffer->addImage(
                new JobOfferImageEntity(
                    jobOffer: $jobOffer,
                    file: $file,
                    isMain: $image->isMain
                )
            );
        }

        $this->manager->flush();
    }


    /**
     * @return JobOffer[]
     */
    public function findAll(string $accountId, string $offerId): array
    {
       $posts = $this->manager->getRepository(JobOfferEntity::class)->findBy([
            "user" => $accountId
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
            "user" => $accountId
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




    #[Override]
    public function findPendingPublications(): array
    {
        return $this->manager->createQueryBuilder()
            ->select('j')
            ->from(JobOfferEntity::class, 'j')
            ->where('j.publicationStatus != :published')
            ->andWhere('j.publicationDate IS NOT NULL')
            ->andWhere('j.publicationDate <= :now')
            ->setParameter(
                'published',
                JobPublicationStatus::PUBLISHED
            )
            ->setParameter(
                'now',
                new \DateTimeImmutable()
            )
            ->getQuery()
            ->getResult();
    }


    #[Override]
    public function isPublicationPending(string $id): bool
    {
        $count = $this->manager->createQueryBuilder()
            ->select('COUNT(j.id)')
            ->from(JobOfferEntity::class, 'j')
            ->where('j.id = :id')
            ->andWhere('j.publicationStatus != :published')
            ->andWhere('j.publicationDate IS NOT NULL')
            ->andWhere('j.publicationDate <= :now')
            ->setParameter('id', $id)
            ->setParameter(
                'published',
                JobPublicationStatus::PUBLISHED
            )
            ->setParameter(
                'now',
                new \DateTimeImmutable()
            )
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }



    #[Override]
    public function save(JobOffer $offer, AccountId $userId): void
    {
        $entity = $this->manager->find(JobOfferEntity::class, $offer->id());

        if (!$entity) {
            $user = $this->manager->getReference(UserEntity::class, $userId->value());
            $entity = $this->mapper->toDoctrine($offer, $user);
            $this->manager->persist($entity);
        }
        else {
            $this->mapper->copy($offer, $entity);
        }
        $this->manager->flush();
    }



    #[Override]
    public function change(JobOffer $jobOffer, string $offerId, string $accountId): void
    {
        throw new \Exception('Not implemented');
    }


    #[Override]
    public function publish(string $offerId, string $userId): void
    {
        $affected = $this->manager
            ->getConnection()
            ->executeStatement(
                "
                UPDATE job_offer
                SET publication_status = :status,
                    updated_at = :updatedAt
                WHERE id = :offerId
                AND user_id = :userId
                ",
                [
                    'status'    => JobPublicationStatus::PUBLISHED->value,
                    'updatedAt' => (new \DateTimeImmutable())
                        ->format('Y-m-d H:i:s'),
                    'offerId'   => $offerId,
                    'userId'    => $userId,
                ]
            );


        if ($affected === 0) {
            throw new \DomainException(
                "Offer not found or user is not owner."
            );
        }
    }




    #[Override]
    public function delete(string $userId, string $uuid): void
    {
        $entity = $this->manager->find(JobOfferEntity::class, $uuid);
        if(!$entity)
            throw new RessourceNotFound();
        
        $this->manager->remove($entity);
        $this->manager->flush();
    }


    
    #[Override]
    public function exists(string $id): bool
    {
        $result = $this->manager
                       ->createQueryBuilder()
                       ->select('1')
                       ->from(JobOfferEntity::class, "j")
                       ->where('j.id = :id')
                       ->setParameter('id', $id)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult();
        return $result !== null;
    }


    public function removeImageFromJob(string $offerId, string $fileName): void
    {
        throw new \Exception('Not implemented');
    }


    #[Override]
    public function hasPublicationDatePassed(string $id): bool
    {
        throw new \Exception('Not implemented');
    }
}

?>