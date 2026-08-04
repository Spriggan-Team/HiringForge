<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Exception\ApplicationNotFoundException;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use DomainException;
use Override;

class JobOfferApplicationRepository
    extends ServiceEntityRepository
    implements ApplicationRepositoryInterface
{

    public function __construct(
        ManagerRegistry $registry
    )
    {
        return parent::__construct($registry, ApplicationEntity::class);
    }

    /**
     * Asserts that an application exists by its ID.
     *
     * @throws ApplicationNotFoundException|\DomainException If the application does not exist.
     */
    public function assertExists(string $id): void
    {
        $exists = (bool) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$exists) {
            throw new \DomainException("Job application with ID '$id' was not found.");
        }
    }

    #[Override]
    public function count(array $criteria): int 
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)');

        //  Filter by Job Posting
        if (isset($criteria['jobOfferId'])) {
            $qb->andWhere('a.jobOffer = :jobOfferId')
            ->setParameter('jobOfferId', $criteria['jobOfferId']);
        }

        // Filter by application status (Enum or String)
        if (isset($criteria['status'])) {
            $qb->andWhere('a.status = :status')
            ->setParameter('status', $criteria['status']);
        }

        //  Filter by company (using the JobOffer -> Company relationship)
        if (isset($criteria['companyId'])) {
            $qb->join('a.jobOffer', 'j')
                ->andWhere('j.company = :companyId') 
                ->setParameter('companyId', $criteria['companyId']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }



    /**
     * Retrieve all applications/postulation related to a specific job
     * and a user (recruiter) or a company if passed.
     *
     * @param string $jobId
     * @param int $limit
     * @param int $skip
     * @param array{
     *      id?: bool,
     *      matchScore?: bool,
     *      status?: bool,
     *      appliedAt?: bool,
     *      candidate?: array{
     *          id?: bool,
     *          lastName?: bool,
     *          firstName?: bool,
     *          email?: bool,
     *          image?: bool|array{
     *              name?: bool,
     *              mime?: bool,
     *              size?: bool,
     *              createdAt?: bool
     *          }
     *      }
     * } $scheme
     * @param string|null $userId
     * @param string|null $companyId
     * @return array
     */
    #[Override]
    public function fetchJobApplicationsProjection(
        string $jobId,
        int $limit = 17,
        int $skip = 0,
        array $scheme = ['id' => true],
        ?string $userId = null,
        ?string $companyId = null
    ): array {
        $qb = $this->createQueryBuilder('a');
        $selectedFields = [];

        // Dynamic selection for application fields
        $allowedApplicationFields = ['id', 'matchScore', 'appliedAt', 'status'];
        foreach ($allowedApplicationFields as $field) {
            if (!empty($scheme[$field])) {
                $selectedFields[] = 'a.' . $field;
            }
        }

        // Set default id field if nothing is required by scheme
        if (empty($selectedFields) && empty($scheme['candidate'])) {
            $selectedFields[] = 'a.id';
        }

        // Handling Sub-Projection for the Candidate Relationship
        if (!empty($scheme['candidate']) && is_array($scheme['candidate'])) {
            $qb->leftJoin('a.candidate', 'c');
            $allowedCandidateFields = ['id', 'firstName', 'lastName', 'email'];

            foreach ($allowedCandidateFields as $candField) {
                if (!empty($scheme['candidate'][$candField])) {
                    $selectedFields[] = 'c.' . $candField . ' AS candidate_' . $candField;
                }
            }

            // Handling Sub-Projection for Candidate's Image (FileEntity)
            if (!empty($scheme['candidate']['image'])) {
                $qb->leftJoin('c.image', 'img');
                $imageScheme = $scheme['candidate']['image'];

                // Backward compatibility: if image is true, retrieve default 'name'
                if ($imageScheme === true) {
                    $selectedFields[] = 'img.name AS candidate_image_name';
                } elseif (is_array($imageScheme)) {
                    $allowedImageFields = ['name', 'mime', 'size', 'createdAt'];
                    foreach ($allowedImageFields as $imgField) {
                        if (!empty($imageScheme[$imgField])) {
                            $selectedFields[] = 'img.' . $imgField . ' AS candidate_image_' . $imgField;
                        }
                    }
                }
            }
        }

        $qb->select(implode(', ', $selectedFields))
           ->where('a.jobOffer = :jobId')
           ->setParameter('jobId', $jobId);

        // Filter by Company or User (Recruiter)
        if ($companyId) {
            $qb->andWhere('a.company = :companyId')
               ->setParameter('companyId', $companyId);
        }
        elseif ($userId) {
            // If filtered by a recruiter, the process goes through the job posting
            $qb->innerJoin('a.jobOffer', 'jo')
               ->andWhere('jo.user = :userId')
               ->setParameter('userId', $userId);
        }

        // Pagination handling
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($skip > 0) {
            $qb->setFirstResult($skip);
        }

        $results = $qb->getQuery()->getArrayResult();

        // Structuring the results if candidate or image fields were requested
        if (!empty($scheme['candidate'])) {
            return array_map(static function (array $row) {
                $candidateData = [];
                $imageData = [];

                foreach ($row as $key => $value) {
                    if (str_starts_with($key, 'candidate_image_')) {
                        $realImgKey = str_replace('candidate_image_', '', $key);
                        $imageData[$realImgKey] = $value;
                        unset($row[$key]);
                    } elseif (str_starts_with($key, 'candidate_')) {
                        $realKey = str_replace('candidate_', '', $key);
                        $candidateData[$realKey] = $value;
                        unset($row[$key]);
                    }
                }

                // Attach nested image object if at least one field is non-null
                if (!empty($imageData) && array_filter($imageData, static fn($v) => $v !== null)) {
                    $candidateData['image'] = $imageData;
                }

                if (!empty($candidateData)) {
                    $row['candidate'] = $candidateData;
                }

                return $row;
            }, $results);
        }

        return $results;
    }




    public function changeStatus(string $applicationId, JobApplicationStatus $newStatus): void
    {
        $application = $this->getEntityManager()->getReference(ApplicationEntity::class, $applicationId);
        if(!$application){
            throw new DomainException("Undentified application, it does not exits in the storage : " . $applicationId);
        }
        $currentStatus = $application->getStatus();

        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new DomainException(sprintf(
                'Transition non autorisée du statut "%s" vers "%s".',
                $currentStatus->value,
                $newStatus->value
            ));
        }

        // Mise à jour effective
        $application->setStatus($newStatus);
        
        $this->entityManager->persist($application);
        $this->entityManager->flush();
    }

}