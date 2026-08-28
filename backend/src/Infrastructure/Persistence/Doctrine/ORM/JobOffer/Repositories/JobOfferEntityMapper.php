<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Domain\File\StaticMedia;
use App\Domain\JobOffer\JobOfferImage;
use App\Domain\JobOffer\JobOffer as DomainEntity;
use App\Domain\Company\CompanyRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\ContractTypeEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferLanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferSkillsEntity;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;


/**
 * This is used for transmitting job offer domain object into its corresponding doctrine entity object & 
 * inversly...
 */
class JobOfferEntityMapper
{

    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private EntityManagerInterface $em
    ){}


    public static function toDomain(JobOfferEntity $entity): DomainEntity
    {
        //  Mapping cat
        $categories = [];
        foreach ($entity->getJobCategories() as $jobCatItem) {
            if ($catItem = $jobCatItem->getCategory()) {
                $categories[] = [
                    'id' => $catItem->getId(),
                    'label' => $catItem->getLabel(),
                ];
            }
        }

        // Mapping des images
        $images = [];
        foreach ($entity->getImages() as $jobImage) {
            if ($img = $jobImage->getFile()) {
                $images[] = JobOfferImage::create(
                    media: StaticMedia::hydrate(
                        id: $img->getId(),
                        name: $img->getName(),
                        size: $img->getSize(),
                        mime: $img->getMime(),
                        originalName: $img->getOriginalName(),
                        createdAt: $img->getCreatedAt()
                    ),
                    isMain: $jobImage->getIsMain()
                );
            }
        }

        // Hydratation complète de l'objet du Domaine
        return DomainEntity::hydrate(
            id: $entity->getId(),
            companyId: $entity->getCompany()->getId(),
            title: $entity->getTitle(),
            content: $entity->getContent(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            publicationStatus: $entity->getPublicationStatus(),
            visibilityStatus: $entity->getVisibilityStatus(),
            activityStatus: $entity->getActivityStatus(),
            categories: $categories,
            images: $images,
            locationId: $entity->getAddress()?->getId(),
            departmentId: $entity->getDepartment()?->getId(),
            jobWorkMode: $entity->getJobWorkMode(),
            contractId: $entity->getContractType()?->getId(),
            minSalary: $entity->getMinSalary(),
            maxSalary: $entity->getMaxSalary(),
            currency: $entity->getCurrency(),
            publicationDate: $entity->getPublicationDate()
        );
    }


    public  function toDoctrine(DomainEntity $offer, UserEntity $user): JobOfferEntity
    {
        $user = $this->em->getReference(UserEntity::class, $user->getId());
        $company = $this->em->getReference(CompanyEntity::class, $offer->companyId());

        $job = JobOfferEntity::create(
            id: $offer->id(),
            user: $user,
            company: $company,
            title: $offer->title(),
            content: $offer->content(),
            minSalary: $offer->minSalary(),
            maxSalary: $offer->maxSalary(),
            currency: $offer->currency(),
            publicationStatus: $offer->publicationStatus()
            
        );

        //-- images
        foreach($offer->images() as $image){
            $job->addImage(
                new JobOfferImageEntity(
                    jobOffer: $job,
                    file: FileEntity::create(
                        name: $image->media->name,
                        mime: $image->media->mime,
                        size: $image->media->size,
                        originalName: $image->media->originalName,
                    ),
                    isMain: $image->isMain
                )
            );
        }

        //-- Contract type
        if($offer->contractId()){
            $contract = $this->em->getReference(ContractTypeEntity::class, $offer->contractId());
            $job->setContractType($contract);
        }

        //-- Department
        if($offer->departmentId()){
            $department = $this->em->getReference(DepartmentEntity::class, $offer->departmentId());
            $job->setDepartment($department);
        }

        //-- Language
        foreach($offer->languages() as $lang){
            $language = $this->em->getReference(LanguageEntity::class,  $lang->language()->id());
            $job->addLanguage(
                JobOfferLanguageEntity::create(
                    jobOffer: $job,
                    language: $language,
                    level: $lang->level()
                )
            );
        }

        //-- Skills
        foreach($offer->skillsId() as $skillId){
            $skill = $this->em->getReference(SkillEntity::class, $skillId);
            $job->addSkill(JobOfferSkillsEntity::create(jobOffer: $job, skill: $skill));
        }

        /** Publication date */
        if($offer->publicationDate()){
            $job->setPublicationDate($offer->publicationDate());
        }

        if($offer->locationId()){
            $address = $this->em->getReference(AddressEntity::class, $offer->locationId());
            $job->setAddress($address);
        }
        else{
            $campanyAddress = $this->companyRepository->fetchUserCompanyProjection(
                userId: $user->getId(),
                scheme: [
                    'address[id]' => true,
                    'address[limit]' => 1
                ]
            );

            if($campanyAddress['address']){
                $address = $this->em->getReference(AddressEntity::class, $campanyAddress["address"]['id']);
                $job->setAddress($address);
            }
        }

        return  $job;
    }



    public function copy(
        DomainEntity $offer,
        JobOfferEntity $doctrine
    ): void {
        // Basics fields
        $doctrine->setTitle($offer->title())
            ->setContent($offer->content())
            ->setActivityStatus($offer->getActivityStatus())
            ->setPublicationStatus($offer->publicationStatus())
            ->setVisibilityStatus($offer->visibilityStatus())
            ->setJobWorkMode($offer->jobWorkMode())
            ->setExpertise($offer->expertise())
            ->setUpdatedAt($offer->updatedAt());

        // publication date
        if ($offer->publicationDate()) {
            $doctrine->setPublicationDate($offer->publicationDate());
        }

        // definir salary
        $doctrine->setCurrency($offer->currency());
        $doctrine->setMinSalary($offer->minSalary());
        $doctrine->setMaxSalary($offer->maxSalary());

        // Relationships by Doctrine References(Proxy)
        if ($offer->departmentId()) {
            $doctrine->setDepartment(
                $this->em->getReference(DepartmentEntity::class, $offer->departmentId())
            );
        } else {
            $doctrine->setDepartment(null);
        }

        if ($offer->contractId()) {
            $doctrine->setContractType(
                $this->em->getReference(ContractTypeEntity::class, $offer->contractId())
            );
        } else {
            $doctrine->setContractType(null);
        }

        if ($offer->locationId()) {
            $doctrine->setAddress(
                $this->em->getReference(AddressEntity::class, $offer->locationId())
            );
        }
    }
}