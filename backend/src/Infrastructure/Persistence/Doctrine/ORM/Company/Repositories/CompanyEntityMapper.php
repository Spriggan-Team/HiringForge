<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\User\Siret;
use App\Domain\File\StaticMedia;
use App\Domain\Company\Company as DomainEntity;
use App\Domain\Department\Department;
use App\Domain\File\TimedMedia;
use App\Domain\Shared\Address;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity; 
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompanyEntityMapper
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {}

    
    public  function toDoctrineEntity(DomainEntity $company, ServiceEntityRepository $serviceEntityRepository): CompanyEntity
    {

        //-- Secure initialization of file entities (null handling
        $logoEntity = null;
        if ($company->logo()) {
            $logoEntity = (new FileEntity())
                ->setName($company->logo()->name)
                ->setMime($company->logo()->mime)
                ->setSize($company->logo()->size);
        }

        $videoEntity = null;
        if ($company->videoPresentation()) {
            $videoEntity = (new FileEntity())
                ->setName($company->videoPresentation()->name)
                ->setMime($company->videoPresentation()->mime)
                ->setSize($company->videoPresentation()->size);
        }

        $entity = CompanyEntity::create(
            id: $company->id(),
            name: $company->name(),
            siret: $company->siret(),
            logo: $logoEntity,
            videoPresentation: $videoEntity
        );

        //-- Address Association
        foreach ($company->address() as $address) {
            $addressEntity = AddressEntity::create(
                city: $address->city,
                street: $address->street,
                postalCode: $address->postalCode,
                country: $address->country
            );

            $entity->addAddress($addressEntity);
        }

        //-- Association of Recruiters Affiliated with the Company
        foreach ($company->recruiters() as $recruiterId) {
            $recruiter = $this->em->getReference(UserEntity::class, $recruiterId);
            $entity->addRecruiters($recruiter);
        }

        //-- Attachment from the secondary image collection
        foreach ($company->images() as $uploadedImage) {
            $image = (new FileEntity())
                ->setName($uploadedImage->name)
                ->setMime($uploadedImage->mime)
                ->setSize($uploadedImage->size);
                  
            $entity->attachToImage($image);
        }

        return $entity;
    }


    //-- convert entity into domain object
    public function toDomainEntity(CompanyEntity $entity): DomainEntity
    {
        $companyId= $entity->getId();

        //-- logo
        $logo = null;
        if ($logoEntity = $entity->getLogo()) {
            $logo = new StaticMedia(
                id: $logoEntity->getId(),
                name: $logoEntity->getName(),
                size: $logoEntity->getSize(),
                mime: $logoEntity->getMime(),
            );
        }

        //-- addresses
        $addresses = [];
        foreach ($entity->getAddresses() as $addressCompanyEntity) {
            $addressDetails = $addressCompanyEntity->getAddress();
            
            if ($addressDetails && $id = $addressDetails->getId()) {
                $addresses[] = Address::create(
                    id: $id,
                    city: $addressDetails->getCity(),
                    country: $addressDetails->getCountry(),
                    street: $addressDetails->getStreet(),
                    postalCode: $addressDetails->getPostalCode(),
                );
            }
        }

        //-- Departments
        $departments = [];
        foreach($entity->getDepartmants() as $department){
            $departments[] = Department::reconstitue(
                id: $department->getId(),
                parent: null,
                companyId: $companyId,
                code: $department->getCode(),
                label: $department->getLabel(),
                isActive: $department->isActive(),
                description: $department->getDescription(),
                externalRef: $department->getExternalRef()
            );
        }

        //--- Extract IDs helper (avoids duplicating foreach loops)
        $extractIds = fn (iterable $collection): array => array_values(
            array_filter(
                array_map(fn ($item) => $item->getId(), is_array($collection) ? $collection : iterator_to_array($collection))
            )
        );

        //--- Deal with images tranformation
        $images = [];
        $mainImage = null;

        foreach($entity->getImages()->toArray() as $companyImg){
            $img = $companyImg->getImage();
            $media = StaticMedia::hydrate(
                id: $img->getId(),
                name: $img->getName(),
                size: $img->getSize(),
                mime: $img->getMime(),
                createdAt: $img->getCreatedAt()
            );
            if($companyImg->isMain()){
                $mainImage = $media;
            }
            else{
                $images[] = $media;
            }    
        }

        //-- Video Address
        $videoPresenatation = null;
        if($entity->getVideoPresentation()){
            $companyVideoPresentationEntity = $entity->getVideoPresentation();
            $videoPresenatation = TimedMedia::hydrate(
                id: $companyVideoPresentationEntity->getId(),
                name: $companyVideoPresentationEntity->getName(),
                size: $companyVideoPresentationEntity->getSize(),
                mime: $companyVideoPresentationEntity->getMime(),
                originalName: $companyVideoPresentationEntity->getOriginalName(),
                createdAt: $companyVideoPresentationEntity->getCreatedAt()
            );
        }

        return DomainEntity::hydrate(
            id: $entity->getId(),
            name: $entity->getName(),
            siret: Siret::hydrate($entity->getSiret()),
            
            address: $addresses,
            departments: $departments,

            logo: $logo,
            images: $images,

            mainImage: $mainImage,
            videoPresentation: $videoPresenatation,

            recruiters: $extractIds($entity->getRecruiters()),
        );
    }



    /**
     * Responsible for synchronizing/updating the existing Doctrine entity with the domain state.
     * Useful for the update scenario in your Repository.
     */
    public function copy(
        DomainEntity $company,
        CompanyEntity $entity
    ): void {
        /*
        * ----------------------
        * Basic data
        * ----------------------
        */

        ApiResponse::$logger->error("Company : ". json_encode($company));

        $entity->setName($company->name());
        $entity->setSiret($company->siret());
        $entity->setDescription($company->description());

        /*
        * ----------------------
        * Logo
        * ----------------------
        */

        $domainLogo = $company->logo();
        $entityLogo = $entity->getLogo();

        if ($domainLogo === null) {

            $entity->setLogo(null);

        } elseif (
            $entityLogo === null
            || (
                $domainLogo->id !== null
                && $entityLogo->getId() !== $domainLogo->id
            )
            || $entityLogo->getName() !== $domainLogo->name
        ) {

            $logoEntity = FileEntity::create(
                name: $domainLogo->name,
                mime: $domainLogo->mime,
                size: $domainLogo->size,
                originalName: $domainLogo->originalName,
            );

            $entity->setLogo($logoEntity);
        }


        /*
        * ----------------------
        * Video presentation
        * ----------------------
        */

        $domainVideo = $company->videoPresentation();
        $entityVideo = $entity->getVideoPresentation();

        if ($domainVideo === null) {

            $entity->attachPresentation(null);

        } elseif (
            $entityVideo === null
            || $entityVideo->getId() !== $domainVideo->id
            || $entityVideo->getName() !== $domainVideo->name
        ) {

            $videoEntity = FileEntity::create(
                name: $domainVideo->name,
                mime: $domainVideo->mime,
                size: $domainVideo->size,
                originalName: $domainVideo->originalName,
            );

            $entity->attachPresentation($videoEntity);
        }

        /*
        * ----------------------
        * Addresses
        * ----------------------
        */

        $this->syncAddresses( $company, $entity);

        /*
        * ----------------------
        * Departments
        * ----------------------
        */

        $this->syncDepartments($company, $entity);

        /*
        * ----------------------
        * Images
        * ----------------------
        */

        $this->syncImages($company, $entity);
    }

    
    /**
     * ------------------------
     *  Helpers
     * ----------------------
     */

    // Synchronize domain address and entity address  
    private function syncAddresses(
        DomainEntity $company,
        CompanyEntity $entity,
    ): void {
        $domainAddresses = $company->address();
        $entityAddresses = $entity->getAddresses();

        /*
        * ----------------------
        * Remove
        * ----------------------
        */

        foreach ($entityAddresses as $companyAddressEntity) {
            $addressEntity = $companyAddressEntity->getAddress();

            $exists = false;

            foreach ($domainAddresses as $domainAddress) {
                if ($addressEntity->getId() === $domainAddress->id) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $entityAddresses->removeElement(
                    $companyAddressEntity
                );
            }
        }


        /*
        * ----------------------
        * Add
        * ----------------------
        */

        foreach ($domainAddresses as $domainAddress) {
            $exists = false;

            foreach ($entityAddresses as $companyAddressEntity) {
                $addressEntity = $companyAddressEntity->getAddress();

                if ($addressEntity->getId() === $domainAddress->id) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $newAddressEntity = AddressEntity::create(
                    street: $domainAddress->street,
                    city: $domainAddress->city,
                    postalCode: $domainAddress->postalCode,
                    country: $domainAddress->country,
                );

                $entity->addAddress($newAddressEntity);
            }
        }
    }


    /** Synchronize department domain & entities */
    private function syncDepartments(
        DomainEntity $company,
        CompanyEntity $entity,
    ): void {
        $domainDepartments = $company->departments();
        $entityDepartments = $entity->getDepartmants();

        /*
        * Index domain departments by ID.
        */
        $domainDepartmentsById = [];

        foreach ($domainDepartments as $domainDepartment) {
            if ($domainDepartment->id() !== null) {
                $domainDepartmentsById[$domainDepartment->id()] = $domainDepartment;
            }
        }

        /*
        * ----------------------
        * Remove
        * ----------------------
        */
        foreach ($entityDepartments->toArray() as $departmentEntity) {
            $id = $departmentEntity->getId();
            if (
                $id !== null
                && !isset($domainDepartmentsById[$id])
            ) {
                $entity->removeDepartment($departmentEntity);
            }
        }

        /*
        * ----------------------
        * Add / Update
        * ----------------------
        */
        foreach ($domainDepartments as $domainDepartment) {
            $id = $domainDepartment->id();

            /*
            * New department
            */
            if ($id === null) {
                $departmentEntity = DepartmentEntity::create(
                    label: $domainDepartment->label(),
                    code: $domainDepartment->code(),
                    description: $domainDepartment->description(),
                    isActive: $domainDepartment->isActive(),
                    externalRef: $domainDepartment->externalRef(),
                    company: $entity,
                );

                $entity->addDepartment($departmentEntity);

                continue;
            }

            /*
            * Existing department
            */
            $departmentEntity = $entityDepartments
                ->filter(
                    static fn (DepartmentEntity $item): bool => $item->getId() === $id
                )
                ->first();

            if ($departmentEntity === false) {
                continue;
            }

            $departmentEntity
                ->setLabel($domainDepartment->label())
                ->setCode($domainDepartment->code())
                ->setDescription($domainDepartment->description())
                ->setIsActive($domainDepartment->isActive())
                ->setExternalRef($domainDepartment->externalRef());

            /*
            * Parent
            */
            $parent = $domainDepartment->parent();

            if ($parent === null) {
                $departmentEntity->setParent(null);
                continue;
            }

            $parentId = $parent->id();

            if ($parentId === null) {
                $departmentEntity->setParent(null);
                continue;
            }

            $parentEntity = $entityDepartments->filter(static fn (DepartmentEntity $item): bool => $item->getId() === $parentId)
                                              ->first();

            if ($parentEntity !== false) {
                $departmentEntity->setParent($parentEntity);
            }
        }
    }


    private function syncImages(
        DomainEntity $company,
        CompanyEntity $entity,
    ): void {
        $domainImages = $company->images();
        $domainMainImage = $company->mainImage();

        /*
        * Build the complete domain image list.
        */
        $allDomainImages = $domainImages;

        if ($domainMainImage !== null) {
            $alreadyExists = false;

            if (!$alreadyExists) {
                foreach ($allDomainImages as $domainImage) {
                    if ($domainImage->name === $domainMainImage->name) {
                        $alreadyExists = true;
                        break;
                    }
                }
            }

            if (!$alreadyExists) {
                $allDomainImages[] = $domainMainImage;
            }
        }

        /*
        * Existing Doctrine relations.
        */
        $entityImages = $entity->getImages();

        /*
        * ----------------------
        * Index domain images by ID
        * ----------------------
        */
        $domainImageIds = [];

        foreach ($allDomainImages as $domainImage) {
            if ($domainImage->id !== null) {
                $domainImageIds[$domainImage->id] = true;
            }
        }

        /*
        * ----------------------
        * Remove
        * ----------------------
        *
        * Every persisted image missing from the complete
        * domain state is considered deleted.
        */
        foreach ($entityImages->toArray() as $companyImageEntity) {
            $fileEntity = $companyImageEntity->getImage();

            if ($fileEntity === null) {
                continue;
            }

            $fileId = $fileEntity->getId();

            if (
                $fileId !== null
                && !isset($domainImageIds[$fileId])
            ) {
                $entity->removeImage($fileEntity);
            }
        }

        /*
        * ----------------------
        * Add / update
        * ----------------------
        */
        foreach ($allDomainImages as $domainImage) {

            /*
            * Determine whether this image must be main.
            */
            $isMain =
                $domainMainImage !== null
                && (
                    $domainMainImage === $domainImage
                    || (
                        $domainMainImage->id !== null
                        && $domainMainImage->id === $domainImage->id
                    )
                );

            /*
            * ----------------------
            * New image
            * ----------------------
            */
            if ($domainImage->id === null) {
                $fileEntity = FileEntity::create(
                    name: $domainImage->name,
                    mime: $domainImage->mime,
                    size: $domainImage->size,
                    originalName: $domainImage->originalName,
                );

                $entity->attachToImage(
                    image: $fileEntity,
                    isMain: $isMain
                );

                continue;
            }

            /*
            * ----------------------
            * Existing image
            * ----------------------
            */
            foreach ($entityImages as $companyImageEntity) {
                $fileEntity = $companyImageEntity->getImage();

                if (
                    $fileEntity === null
                    || $fileEntity->getId() !== $domainImage->id
                ) {
                    continue;
                }

                $companyImageEntity->setIsMain($isMain);

                break;
            }
        }

        /*
        * Final normalization.
        *
        * Ensures every existing image is non-main except
        * the actual domain main image.
        */
        if ($domainMainImage !== null) {
            foreach ($entity->getImages() as $companyImageEntity) {
                $fileEntity = $companyImageEntity->getImage();

                if ($fileEntity === null) {
                    continue;
                }
                
                $companyImageEntity->setIsMain(
                    ($domainMainImage->id !== null && $fileEntity->getId() === $domainMainImage->id)
                    || ($domainMainImage->id === null && $fileEntity->getName() === $domainMainImage->name)
                );
            }
        } else {
            /*
            * No main image in the domain.
            */
            foreach ($entity->getImages() as $companyImageEntity) {
                $companyImageEntity->setIsMain(false);
            }
        }
    }

}