<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Domain\JobOffer\JobOfferImage;
use App\Domain\JobOffer\JobOfferImageRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper\FileEntityMapper;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Override;

class JobOfferImageRepository extends ServiceEntityRepository implements JobOfferImageRepositoryInterface
{
    private EntityManagerInterface $em;


    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, JobOfferImageEntity::class);
        $this->em = $em;
    }

    #[Override]
    public function unsetMainImage(string $jobId): void
    {
        $this->getEntityManager()->createQuery(
            'UPDATE App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity joi 
            SET joi.isMain = false 
            WHERE joi.jobOffer = :jobId 
            AND joi.isMain = true'
        )
        ->setParameter('jobId', $jobId)
        ->execute();
    }

    
    #[Override]
    public function associateImagesWithJob(string $offerId, array $images): void
    {
        if (empty($images)) {
            return;
        }

        $jobOffer = $this->em->find(JobOfferEntity::class, $offerId);

        if (!$jobOffer) {
            throw new \DomainException(sprintf('L\'offre d\'emploi ID "%s" n\'existe pas.', $offerId));
        }

        foreach ($images as $image) {
            $file = FileEntity::create(
                name: $image->media->name,
                mime: $image->media->mime,
                size: $image->media->size,
                originalName: $image->media->originalName
            );

            $jobOfferImage = new JobOfferImageEntity(
                jobOffer: $jobOffer,
                file: $file,
                isMain: $image->isMain
            );

            $jobOffer->addImage($jobOfferImage);
        }

        $this->em->flush();
    }


    #[Override]
    public function isImagesAssociatedWithJob(string $offerId, array $images): bool
    {
        if (empty($images)) {
            return true;
        }

        $count = (int) $this->createQueryBuilder('joi')
            ->select('COUNT(joi.id)')
            ->innerJoin('joi.file', 'f')
            ->where('joi.jobOffer = :offerId')
            ->andWhere('f.id IN (:fileIds)')
            ->setParameter('offerId', $offerId)
            ->setParameter('fileIds', $images)
            ->getQuery()
            ->getSingleScalarResult();

        return $count === count($images);
    }


    #[Override]
    public function removeImagesFromJob(string $offerId, array $images): void
    {
        if (empty($images)) {
            return;
        }

        $entities = $this->createQueryBuilder('joi')
            ->innerJoin('joi.file', 'f')
            ->where('joi.jobOffer = :offerId')
            ->andWhere('f.id IN (:fileIds)')
            ->setParameter('offerId', $offerId)
            ->setParameter('fileIds', $images)
            ->getQuery()
            ->getResult();

        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->em->flush();
    }


    #[Override]
    public function getMainImage(string $jobId): ?JobOfferImage
    {
        /** @var JobOfferImageEntity|null $entity */
        $entity = $this->createQueryBuilder('joi')
            ->where('joi.jobOffer = :jobId')
            ->andWhere('joi.isMain = true')
            ->setParameter('jobId', $jobId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$entity) {
            return null;
        }

        // Conversion vers le ValueObject / Entité Domaine
        return new JobOfferImage(
            media:FileEntityMapper::toStaticDomainMedia(
                $entity->getFile()
            ),
            isMain: $entity->getIsMain()
        );
    }
}