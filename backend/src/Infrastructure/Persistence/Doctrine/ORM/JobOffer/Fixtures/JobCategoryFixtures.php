<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Fixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\CategoryEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Category\Fixtures\CategoryFixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobCategoryEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;



class JobCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $categories = CategoryFixtures::CATEGORIES;
        $jobsCount  = count(JobOfferFixtures::JOBS);

        for ($i = 0; $i < $jobsCount; $i++) {
            /** @var JobOfferEntity $job */
            $job = $this->getReference('job_'.$i, JobOfferEntity::class);

            $assignedCategories = array_slice(
                $categories,
                $i % count($categories),
                random_int(1, 3)
            );

            foreach ($assignedCategories as $categoryName) {
                $jobCategory = (new JobCategoryEntity())
                    ->setJobOffer($job)
                    ->setCategory(
                        $this->getReference(
                            'category_'.$categoryName,
                            CategoryEntity::class
                        )
                    );

                $manager->persist($jobCategory);
            }
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
