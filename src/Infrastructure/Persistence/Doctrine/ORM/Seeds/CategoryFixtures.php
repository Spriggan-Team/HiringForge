<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\CategoryEntity;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class CategoryFixtures extends Fixture
{
    public const CATEGORIES = [
        'Backend',
        'Frontend',
        'DevOps',
        'Data',
        'AI',
        'Mobile',
        'Security',
        'Marketing',
        'Design',
        'Management',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $name) {
            $category = (new CategoryEntity())->setName($name);
            $manager->persist($category);
            $this->addReference('category_'.$name, $category);
        }

        $manager->flush();
    }
}
