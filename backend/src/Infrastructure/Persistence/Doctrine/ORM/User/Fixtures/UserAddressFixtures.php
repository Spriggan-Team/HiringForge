<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Fixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class UserAddressFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (UserFixtures::USERS as $i => $_) {

            /** @var UserEntity $user */
            $user = $this->getReference('user_'.$i, UserEntity::class);

            $address = AddressEntity::create(
                street: 'Rue '.$i,
                postalCode: '7500'.$i,
                country: 'France'
            );

            // Cascade depuis User
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
