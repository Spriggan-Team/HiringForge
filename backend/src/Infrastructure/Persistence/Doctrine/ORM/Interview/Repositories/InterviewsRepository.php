<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;

use Override;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;



class InterviewsRepository extends ServiceEntityRepository 
    implements InterviewsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterviewEntity::class);
    }
    
    
    /**
     * Retrieve interviews related to a specific job linked to a recruiter (user),
     * a company, or a specific candidate.
     *
     * @param string $jobId
     * @param int $limit
     * @param int $skip
     * @param array{
     *      id?: bool,
     *      startDate?: bool,
     *      minutes?: bool,
     *      title?: bool,
     * 
     *      description?: bool,
     *      status?: bool,
     *      url?: bool,
     *      candidate?: array{
     *          id?: bool,
     *          firstName?: bool,
     *          lastName?: bool,
     *          email?: bool,
     *          image?: bool|array{
     *              id?: bool,
     *              name?: bool,
     *              size?: bool,
     *              mime?: bool
     *          }
     *      }
     * } $scheme
     * @param string|null $userId
     * @param string|null $companyId
     * @param string|null $candidateId
     * @return array
     */
    #[Override]
    public function fetchJobInterviewsProjection(
        string $jobId,
        int $limit = 17,
        int $skip = 0,
        array $scheme = ['id' => true],
        ?string $userId = null,
        ?string $companyId = null,
        ?string $candidateId = null
    ): array {
        $qb = $this->createQueryBuilder('i');
        $selectedFields = [];

        //  Dynamic selection for Interview entity fields
        $allowedInterviewFields = ['id', 'title' , 'startDate', 'minutes', 'description', 'status', 'url'];
        foreach ($allowedInterviewFields as $field) {
            if (!empty($scheme[$field])) {
                $selectedFields[] = 'i.' . $field;
            }
        }

        // Default to id if no root fields or sub-relations are requested
        if (empty($selectedFields) && empty($scheme['candidate'])) {
            $selectedFields[] = 'i.id';
        }

        //  Sub-Projection for Candidate relationship
        if (!empty($scheme['candidate']) && is_array($scheme['candidate'])) {
            $qb->leftJoin('i.candidate', 'c');
            $allowedCandidateFields = ['id', 'firstName', 'lastName', 'email'];

            foreach ($allowedCandidateFields as $candField) {
                if (!empty($scheme['candidate'][$candField])) {
                    $selectedFields[] = 'c.' . $candField . ' AS candidate_' . $candField;
                }
            }

            // Sub-Projection for Candidate's Profile Image (FileEntity)
            if (!empty($scheme['candidate']['image'])) {
                $qb->leftJoin('c.image', 'img');
                $imageScheme = $scheme['candidate']['image'];

                if ($imageScheme === true) {
                    $selectedFields[] = 'img.name AS candidate_image_name';
                } elseif (is_array($imageScheme)) {
                    $allowedImageFields = ['id', 'name', 'size', 'mime'];
                    foreach ($allowedImageFields as $imgField) {
                        if (!empty($imageScheme[$imgField])) {
                            $selectedFields[] = 'img.' . $imgField . ' AS candidate_image_' . $imgField;
                        }
                    }
                }
            }
        }

        $qb->select(implode(', ', $selectedFields))
           ->where('i.jobOffer = :jobId')
           ->setParameter('jobId', $jobId);

        //  Filtering by Candidate, Company or Recruiter User (Exclusive execution)
        if ($candidateId) {
            $qb->andWhere('i.candidate = :candidateId')
               ->setParameter('candidateId', $candidateId);
        } elseif ($companyId) {
            $qb->andWhere('i.company = :companyId')
               ->setParameter('companyId', $companyId);
        } elseif ($userId) {
            $qb->innerJoin('i.jobOffer', 'jo')
               ->andWhere('jo.user = :userId')
               ->setParameter('userId', $userId);
        }

        //  Pagination
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($skip > 0) {
            $qb->setFirstResult($skip);
        }

        $results = $qb->getQuery()->getArrayResult();

        //  Restructuring data tree for candidate and nested image
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




    public function count(array $criteria): int 
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.id)');

        //-- Filter by status
        if (!empty($criteria['status'])) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        // Direct filter : check if an iterviews posses a relation with the procided offer
        if (!empty($criteria['jobOfferId'])) {
            $qb->andWhere('i.jobOffer = :jobOfferId')
               ->setParameter('jobOfferId', $criteria['jobOfferId']);
        }

        // Filter By Compoany par Company (companyId)
        if (!empty($criteria['companyId'])) {
            $qb->innerJoin('i.jobOffer', 'j');
  
            $qb->andWhere('j.company = :companyId')
                ->setParameter('companyId', $criteria['companyId']);
        }

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        }
        catch (NoResultException | NonUniqueResultException) {
            return 0;
        }
    }
}