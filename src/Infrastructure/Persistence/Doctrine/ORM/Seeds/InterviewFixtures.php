<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Domain\Interviews\InterviewStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity;

use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;



class InterviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $interview = (new InterviewEntity())
            ->setId(Uuid::uuid4()->toString())
            ->setCandidate($this->getReference(CandidateFixtures::CANDIDATE_1, CandidateEntity::class))
            ->setJobOffer($this->getReference(JobOfferFixtures::JOB_1, JobOfferEntity::class))
            ->setStartDate(new \DateTimeImmutable('+2 days'))
            ->setDuration(60)
            ->setStatus(InterviewStatus::SCHEDULED);

        $manager->persist($interview);
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
