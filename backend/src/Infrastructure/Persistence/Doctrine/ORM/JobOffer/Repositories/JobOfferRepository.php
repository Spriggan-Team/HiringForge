<?php 

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories\Mapper\JobOfferEntityMapper;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Override;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Doctrine\ORM\EntityManagerInterface;


class JobOfferRepository implements JobOfferRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
        private JobOfferEntityMapper $mapper
    ){}

    
    #[Override]
    public function addView(
        string $candidateId,
        string $jobOfferId
    ): void {
        $candidate = $this->manager->getReference(
            CandidateEntity::class,
            $candidateId
        );

        $jobOffer = $this->manager->getReference(
            JobOfferEntity::class,
            $jobOfferId
        );

        if(!$candidate || !$jobOffer){ 
            throw new \Exception("Candiate & Joboffer not found -> view cannot be created"); 
        }

        $view = JobOfferViewEntity::create(
            candidate: $candidate,
            jobOffer: $jobOffer
        );
        

        $this->manager->persist($view);
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


    #[Override]
    public function getAuthorId(string $jobId): string
    {
        $result = $this->manager->createQueryBuilder()
            ->select('IDENTITY(j.user) AS authorId')
            ->from(JobOfferEntity::class, 'j')
            ->where('j.id = :jobId')
            ->setParameter('jobId', $jobId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null || $result['authorId'] === null) {
            throw new ResourceNotFoundException(
                "Author of job id not found"
            );
        }

        return (string) $result['authorId'];
    }
    

    #[Override]
    public function getTitle(string $jobId): string
    {
        $qb = $this->manager->createQueryBuilder()
            ->select('j.title')
            ->from(JobOfferEntity::class, 'j')
            ->where('j.id = :jobId')
            ->setParameter('jobId', $jobId)
            ->getQuery();

        $title = $qb->getOneOrNullResult();

        if ($title === null) {
            throw new ResourceNotFoundException(
                sprintf('Job offer "%s" was not found.', $jobId)
            );
        }

        return $title['title'];
    }


    #[Override]
    public function canAcceptApplications(string $jobOfferId): bool
    {
        return (bool) $this->manager
                           ->createQueryBuilder()
                           ->select('1')
                           ->from(JobOfferEntity::class, 'j')
                           ->where('j.id = :jobId')
                           ->andWhere('j.publicationStatus = :publicationStatus')
                           ->andWhere('j.visibilityStatus = :visibilityStatus')
                           ->setParameter('jobId', $jobOfferId)
                           ->setParameter('publicationStatus', JobPublicationStatus::PUBLISHED)
                           ->setParameter('visibilityStatus', JobOfferVisibilityStatus::PUBLIC)
                           ->setMaxResults(1)
                           ->getQuery()
                           ->getOneOrNullResult();
    }


    #[Override]
    public function canDefineAsDraft(JobOffer|string $job): bool
    {
        $jobOffer = is_string($job) ? $this->manager->find(JobOfferEntity::class, $job) : $job;

        if (!$jobOffer) {
            return false;
        }

        //-- Already closed offer
        if ($jobOffer->getPublicationStatus() === JobPublicationStatus::CLOSED) {
            return false;
        }

        // Verify abscence of applications
        $applicationsCount = (int) $this->manager->createQueryBuilder()
            ->select('COUNT(a.id)')
            ->from(JobOfferEntity::class, 'j')
            ->leftJoin('j.applications', 'a')
            ->where('j.id = :jobId')
            ->setParameter('jobId', $jobOffer->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $applicationsCount === 0;
    }

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
           throw new ResourceNotFoundException("Ressource not found"); 
        }
        return JobOfferEntityMapper::toDomain($entity);
    }


    #[Override]
    public function getCompanyId(string $jobOfferId): string
    {
        $companyId = $this->manager
            ->createQueryBuilder()
            ->select('IDENTITY(j.company)')
            ->from(JobOfferEntity::class, 'j')
            ->where('j.id = :jobId')
            ->setParameter('jobId', $jobOfferId)
            ->getQuery()
            ->getSingleScalarResult();

        return (string) $companyId;
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
    public function isEditable(string $jobId): bool
    {
        $result = $this->manager->createQuery(
            'SELECT j.publicationStatus 
            FROM App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity j 
            WHERE j.id = :id'
        )
        ->setParameter('id', $jobId)
        ->getOneOrNullResult();

        if ($result === null) {
            return false;
        }

        $status = $result['publicationStatus'];

        return !in_array($status, [
            JobPublicationStatus::PUBLISHED,
            JobPublicationStatus::CLOSED,
        ], true);
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
    public function save(JobOffer $offer, string $userId): void
    {
        $entity = $this->manager->find(JobOfferEntity::class, $offer->id());

        if (!$entity) {
            $user = $this->manager->getReference(UserEntity::class, $userId);
            $entity = $this->mapper->toDoctrine($offer, $user);
            $this->manager->persist($entity);
        }
        else {
            $this->mapper->copy($offer, $entity);
        }
        $this->manager->flush();
    }




    #[Override]
    public function delete(string $userId, string $uuid): void
    {
        $entity = $this->manager->find(JobOfferEntity::class, $uuid);
        if(!$entity)
            throw new ResourceNotFoundException();
        
        $this->manager->remove($entity);
        $this->manager->flush();
    }



    #[Override]
    public function updatePublicationStatusDirectly(
        string $jobId, 
        JobPublicationStatus $prevStatus, 
        JobPublicationStatus $newStatus
    ): bool {
        $affectedRows = $this->manager->createQueryBuilder()
            ->update()
            ->from(JobOfferEntity::class, 'j')
            ->set('j.publicationStatus', ':newStatus')
            ->where('j.id = :id')
            ->andWhere('j.publicationStatus = :prevStatus')
            ->setParameters([
                'id' => $jobId,
                'prevStatus' => $prevStatus->value,
                'newStatus' => $newStatus->value,
            ])
            ->getQuery()
            ->execute();

        return $affectedRows > 0;
    }




    #[Override]
    public function hasPublicationDatePassed(string $id): bool
    {
        throw new \Exception('Not implemented');
    }
}

?>