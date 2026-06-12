<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Company\Repositories;

use App\Domain\Company\Company as DomainEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity; 
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Doctrine\ORM\EntityManagerInterface;



class CompanyEntityMapper
{
    public static function toDoctrineEntity(DomainEntity $company, EntityManagerInterface $em): CompanyEntity
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
            $recruiter = $em->getReference(UserEntity::class, $recruiterId);
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

    /**
     * Responsible for synchronizing/updating the existing Doctrine entity with the domain state.
     * Useful for the update scenario in your Repository.
     */
    public static function copy(DomainEntity $company, CompanyEntity $entity, EntityManagerInterface $em): void
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
            foreach ($existingAddresses as $existingAddress) {
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