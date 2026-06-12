<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Fixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Fixtures\FileFixtures;
use App\Infrastructure\Persistence\Doctrine\ORM\User\Fixtures\UserFixtures;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class UserImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $imageIndexMax = FileFixtures::IMAGE_COUNT - 1;

        foreach (UserFixtures::USERS as $i => $_) {
            /** @var UserEntity $user */
            $user = $this->getReference('user_'.$i, UserEntity::class);

            $nbImages = random_int(1, 3);
            $usedImages = [];

            for ($j = 0; $j < $nbImages; $j++) {
                do {
                    $imageIndex = random_int(0, $imageIndexMax);
                } while (in_array($imageIndex, $usedImages, true));

                $usedImages[] = $imageIndex;

                /** @var FileEntity $image */
                $image = $this->getReference('image_'.$imageIndex, FileEntity::class);

                // Création correcte avec le constructeur
                // $userImage = new CompanyIma($user, $image);

                // $manager->persist($userImage);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            FileFixtures::class,
        ];
    }
}
