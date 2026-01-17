<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\CategoryEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobCategoryEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class JobCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $job = $this->getReference(JobOfferFixtures::JOB_1, JobOfferEntity::class);

        foreach (['IT', 'Finance'] as $cat) {
            $jc = (new JobCategoryEntity())
                ->setJobOffer($job)
                ->setCategory($this->getReference('category_'.$cat, CategoryEntity::class));

            $manager->persist($jc);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            JobOfferFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
