<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Fixtures;

use App\Domain\Interviews\InterviewStatus;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Fixtures\CandidateFixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Fixtures\JobOfferFixtures;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;



class InterviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $interview = (new InterviewEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setCandidate($this->getReference('candidate_'.$i, CandidateEntity::class))
                ->setJobOffer($this->getReference('job_'.$i, JobOfferEntity::class))
                ->setStartDate(new \DateTimeImmutable('+'.($i + 1).' days'))
                ->setDuration(45)
                ->setStatus(InterviewStatus::SCHEDULED);

            $manager->persist($interview);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CandidateFixtures::class,
            JobOfferFixtures::class,
        ];
    }
}

