<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\User\Siret;
use App\Domain\File\StaticMedia;
use App\Domain\Company\Company as DomainEntity;
use App\Domain\Shared\Address;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity; 
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class CompanyEntityMapper
{
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
                street: $address->street,
                postalCode: $address->postalCode,
                country: $address->country
            );

            $entity->addAddress($addressEntity);
        }

        //-- Association of Recruiters Affiliated with the Company
        foreach ($company->recruiters() as $recruiterId) {
            $recruiter = $serviceEntityRepository->getEntityManager()->getReference(UserEntity::class, $recruiterId);
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
    public function toDomainEntity(CompanyEntity $entity): DomainEntity{

        //-- logo
        $logo = null;
        if($entity->getLogo()){
            $logoEntity = $entity->getLogo();
            $logo = new StaticMedia(
                id: $logoEntity->getId(),
                name: $logoEntity->getName(),
                size: $logoEntity->getSize(),
                mime: $logoEntity->getMime(),
            );
        }

        //-- address[]
        $address = [];
        foreach($entity->getAddresses() as $addressCompanyEntity){
            $addressDetails = $addressCompanyEntity->getAdrdress();
            $id = $addressDetails->getId();
            if($id){
                $address[] =  Address::create(
                    id: $id,
                    country: $addressDetails->getCountry(),
                    street: $addressDetails->getStreet(),
                    postalCode: $addressDetails->getPostalCode(),
                );
            }
        }


        //-- images
        $images = [];
        foreach($entity->getImages() as $imageEntity){
            $id = $imageEntity->getId();
            if($id)
               $images[] = $id;
        }

        //-- recruiters
        $recruiters = [];
        foreach($entity->getRecruiters() as $recruiterEntity){
            $id = $recruiterEntity->getId();
            if($id)
                $recruiters[] = $id;
        }

        return DomainEntity::hydrate(
            id: $entity->getId(),
            name: $entity->getName(),
            siret: Siret::hydrate($entity->getSiret()),
            address: $address,
            images: $images,
            logo: $logo,
            recruiters: $recruiters
        );

    }


    /**
     * Responsible for synchronizing/updating the existing Doctrine entity with the domain state.
     * Useful for the update scenario in your Repository.
     */
    public  function copy(DomainEntity $company, CompanyEntity $entity): void
    {
        //--  Updating master data
        $entity->setName($company->name());
        $entity->setSiret($company->siret());


        //-- Logo Synchronization Logic
        if ($company->logo()) {
            if (!$entity->getLogo() || $entity->getLogo()->getName() !== $company->logo()->name) {
                $logoEntity = (new FileEntity())
                    ->setName($company->logo()->name)
                    ->setMime($company->logo()->mime)
                    ->setSize($company->logo()->size);
                $entity->setLogo($logoEntity);
            }
        } else {
            $entity->setLogo(null);
        }


       // Address synchronization logic (Prevents duplicates and handles removals)
        $existingAddresses = $entity->getAddresses();
        

        foreach ($company->address() as $domainAddress) {
            $alreadyExists = false;
            foreach ($existingAddresses as $existingCompanyAddress) {
                $existingAddress = $existingCompanyAddress->getAdrdress();
                if (
                    $existingAddress->getStreet() === $domainAddress->street &&
                    $existingAddress->getPostalCode() === $domainAddress->postalCode
                ) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists) {
                $newAddressEntity = AddressEntity::create(
                    street: $domainAddress->street,
                    postalCode: $domainAddress->postalCode,
                    country: $domainAddress->country
                );
                $entity->addAddress($newAddressEntity);
            }
        }
    }

}