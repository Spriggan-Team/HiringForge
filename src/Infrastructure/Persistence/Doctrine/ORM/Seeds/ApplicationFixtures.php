<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;



class ApplicationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $application = new ApplicationEntity(
            $this->getReference(CandidateFixtures::CANDIDATE_1, CandidateEntity::class),
            $this->getReference(JobOfferFixtures::JOB_1, JobOfferEntity::class)
        );

        $application->setId(Uuid::uuid4()->toString());

        $manager->persist($application);
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
