<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\Candidate\Candidate as Domain;
use App\Domain\Candidate\CandidateId;
use App\Domain\Candidate\CandidateSkillRepositoryInterface;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;

class CandidateEntityMapper
{
    public function __construct(
        private CandidateSkillRepositoryInterface $candidateSkillRepo,
    ) {}


    public  function toDomain(
        CandidateEntity $entity,
    ): Domain
    {
        $candidateId = CandidateId::hydrate($entity->getId());
        $staticImage = null;
        
        //-- Images
        $image = $entity->getImage() ;
        if($image){
            $staticImage = new StaticMedia(
                name: $image->getName(),
                size: $image->getSize(),
                mime: $image->getMime()
            );
        }

        //-- Skills
        $skills = $this->candidateSkillRepo->getCandidateSkills($entity->getId());

        //-- Resumes
        $cvs = [];
        $candidateResumes = $entity->getResumes();

        foreach($candidateResumes as $cv){
            $resume = $cv->getFile();
            $cvs[] =  StaticMedia::hydrate(
                id: $resume->getId(),
                name: $resume->getName(), 
                size: $resume->getSize(),
                mime: $resume->getMime(),
                originalName: $resume->getOriginalName(),
                createdAt: $resume->getCreatedAt()
            );
        }

        //-- Address
        $address = null;
        $addressEntity = $entity->getAddress();
        if($addressEntity){
            $address = Address::hydrate(
                id: $addressEntity->getId(),
                city: $addressEntity->getCity(),
                street: $addressEntity->getStreet(),
                country: $addressEntity->getCountry(),
                postalCode: $addressEntity->getPostalCode(),
            );
        }

        return Domain::create(
            id: $candidateId->value(),
            firstName: $entity->getFirstName(),
            lastName: $entity->getLastName(),
            email: EmailAddress::hydrate($entity->getEmail()),
            passwordHash: $entity->getPassword(),
            image: $staticImage,
            cvs: $cvs,
            address: $address,
            skills: $skills,
            searchRadius: $entity->getSearchRadius()
        );
    }



    public  function toEntity(Domain $candidate): CandidateEntity
    {
        $entity = new CandidateEntity();
        $entity->setId($candidate->id())
               ->setEmail($candidate->email())
               ->setPassword($candidate->passwordHash())
               ->setFirstName($candidate->firstName())
               ->setLastName($candidate->lastName())
               ->setDescription($candidate->description())
               ->setSearchRadius($candidate->searchRadius());

        $address = $candidate->address();
        if($address){
            $addressEntity = new AddressEntity();
            $addressEntity->setCountry($address->country)
                          ->setPostalCode($address->postalCode)
                          ->setStreet($address->street)
                          ->setCity($address->city);

            $entity->attachToAddress($addressEntity);
        }

        //-- Resume
        $cvs = $candidate->cvs();
        foreach($cvs as $cv){
            $fileEntity = new FileEntity();
            $fileEntity->setName($cv->name)
                       ->setSize($cv->size)
                       ->setMime($cv->mime)
                       ->setOriginalName($cv->originalName ?? null);
            $resume =  CandidateResumeEntity::create(
                candidate: $entity,
                file: $fileEntity
            );
     
            $entity->addResume($resume);
        }


        $image = $candidate->image();
        if($image){
            $fileEntity = new FileEntity();
            $fileEntity->setName($image->name)
                       ->setSize($image->size)
                       ->setMime($image->mime)
                       ->setOriginalName($image->originalName ?? null);
            $entity->attachImage($fileEntity);
        }

        return $entity;
    }



    public function updateEntity(
        Domain $domain,
        CandidateEntity $entity,
    ): CandidateEntity {
        // Basic data
        $entity
            ->setLastName($domain->lastName())
            ->setFirstName($domain->firstName())
            ->setEmail($domain->email())
            ->setDescription($domain->description());

        // Address
        $addressDomain = $domain->address();

        if ($addressDomain !== null) {
            $address = $entity->getAddress();

            if ($address === null) {
                $address = AddressEntity::create(
                    city: $addressDomain->city,
                    country: $addressDomain->country,
                    postalCode: $addressDomain->postalCode,
                    street: $addressDomain->street,
                );

                $entity->setAddress($address);
            } else {
                $address
                    ->setCity($addressDomain->city)
                    ->setCountry($addressDomain->country)
                    ->setPostalCode($addressDomain->postalCode)
                    ->setStreet($addressDomain->street);
            }
        }

        return $entity;
    }

}