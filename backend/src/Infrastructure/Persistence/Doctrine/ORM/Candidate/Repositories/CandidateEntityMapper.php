<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\Candidate as Domain;
use App\Domain\Candidate\CandidateId;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\EmailAddress;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;

class CandidateEntityMapper
{
    public static function toDomain(CandidateEntity $entity): Domain
    {
        $candidateId =  CandidateId::hydrate($entity->getId());
        $staticImage = null;
        
        $image = $entity->getImage() ;
        if($image){
            $staticImage = new StaticMedia(
                $image->getOriginalName(),
                $image->getSize(),
                $image->getMime()
            );
        }

        $cv = $entity->getCV();
        $staticCv = null;

        if($cv){
            $staticCv = new StaticMedia(
                $cv->getOriginalName(), 
                $cv->getSize(),
                $cv->getMime()
            );
        }

        return Domain::create(
            id: $candidateId,
            firstName: $entity->getFirstName(),
            lastName: $entity->getLastName(),
            email: EmailAddress::hydrate($entity->getEmail()),
            passwordHash: $entity->getPassword(),
            image: $staticImage,
            cv: $staticCv,
            searchRadius: $entity->getSearchRadius()
        );
    }



    public static function toEntity(Domain $candidate): CandidateEntity
    {
        $entity = new CandidateEntity();
        $entity->setEmail($candidate->email())
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
                        ->setStreet($address->street);

            $entity->attachToAddress($addressEntity);
        }

        $cv = $candidate->cv();
        if($cv)
        {
            $fileEntity = new FileEntity();
            $fileEntity->setOriginalName($cv->name)
                       ->setSize($cv->size)
                       ->setMime($cv->mime);
            $entity->attachCV($fileEntity);
        }

        $image = $candidate->image();
        if($image){
            $fileEntity = new FileEntity();
            $fileEntity->setOriginalName($image->name)
                       ->setSize($image->size)
                       ->setMime($image->mime);
            $entity->attachImage($fileEntity);
        }

        return $entity;
    }


}