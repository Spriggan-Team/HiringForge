<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferFixtures;

use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;



class ApplicationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $application = new ApplicationEntity(
                $this->getReference('candidate_'.($i % 10), CandidateEntity::class),
                $this->getReference('job_'.($i % 20), JobOfferEntity::class)
            );

            $application->setId(Uuid::uuid4()->toString());
            $manager->persist($application);
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
