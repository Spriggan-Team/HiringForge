<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Fixtures;

use App\Domain\User\UserId;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class UserFixtures extends Fixture
{
    public const USERS = [
        ['TechNova', 'contact@technova.io'],
        ['FinEdge', 'hr@finedge.com'],
        ['GreenFuture', 'jobs@greenfuture.org'],
        ['Cloudify', 'careers@cloudify.io'],
        ['DataPulse', 'jobs@datapulse.ai'],
        ['HealthPlus', 'recruit@healthplus.fr'],
        ['AutoDrive', 'talent@autodrive.com'],
        ['RetailPro', 'jobs@retailpro.eu'],
        ['EduSmart', 'hr@edusmart.com'],
        ['CyberShield', 'careers@cybershield.io'],
        ['MediaSpark', 'jobs@mediaspark.tv'],
        ['FinTrust', 'recruitment@fintrust.com'],
        ['SmartCity', 'jobs@smartcity.io'],
        ['AgroTech', 'talent@agrotech.fr'],
        ['BioLife', 'jobs@biolife.org'],
        ['TravelEase', 'careers@travelease.com'],
        ['LogistiX', 'jobs@logistix.io'],
        ['PayFlow', 'hr@payflow.com'],
        ['EcoBuild', 'jobs@ecobuild.fr'],
        ['GameForge', 'jobs@gameforge.io'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $i => [$name, $email]) {

            $address = AddressEntity::create(
                street: '1 rue de la République',
                postalCode: '75001',
                country: 'France'
            );

            $user = UserEntity::create(
                id: (UserId::create())->value(),
                name: $name,
                email: $email,
                password: '$2y$10$fixtureHashPassword1234567890',
                siret: str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT),
                address: $address
            );

            // 👉 UN SEUL persist
            $manager->persist($user);

            $this->addReference('user_'.$i, $user);
        }

        $manager->flush();
    }
}

