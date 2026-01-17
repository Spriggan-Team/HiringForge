<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Domain\JobOffer\JobStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity;
use Ramsey\Uuid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;



class JobOfferFixtures extends Fixture implements DependentFixtureInterface
{
    public const JOB_1 = 'job_1';
    public const JOB_2 = 'job_2';

    public function load(ObjectManager $manager): void
    {
        foreach ([self::JOB_1, self::JOB_2] as $i => $ref) {
            $job = (new JobOfferEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setTitle("Job offer $i")
                ->setContent(['description' => 'Lorem ipsum'])
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setStatus(JobStatus::PUBLISHED)
                ->setUser($this->getReference(UserFixtures::USER_1, UserEntity::class));

            $manager->persist($job);
            $this->addReference($ref, $job);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
