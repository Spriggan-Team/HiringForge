<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity;
use Ramsey\Uuid\Uuid;
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
            $user = (new UserEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setName($name)
                ->setEmail($email)
                ->setPassword('$2y$10$fixtureHashPassword1234567890')
                ->setSiret(str_pad((string) random_int(1, 99999999999999), 14, '0', STR_PAD_LEFT));

            $manager->persist($user);
            $this->addReference('user_'.$i, $user);
        }

        $manager->flush();
    }
}
