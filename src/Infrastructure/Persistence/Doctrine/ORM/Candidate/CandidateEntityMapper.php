<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Domain\Candidate\Candidate as Domain;
use App\Domain\Candidate\CandidateId;
use App\Domain\File\StaticMedia;

class CandidateEntityMapper
{
    public static function toDomain(CandidateEntity $entity): Domain
    {
        $candidateId =  new CandidateId($entity->getId());
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
        $staticCv = new StaticMedia(
            $cv->getOriginalName(),
            $cv->getSize(),
            $cv->getMime()
        );

        return Domain::create(
            id: $candidateId,
            firstName: $entity->getFirstName(),
            lastName: $entity->getLastName(),
            email: $entity->getEmail(),
            passwordHash: $entity->getPassword(),
            image: $staticImage,
            cv: $staticCv,
        );
    }

    
}