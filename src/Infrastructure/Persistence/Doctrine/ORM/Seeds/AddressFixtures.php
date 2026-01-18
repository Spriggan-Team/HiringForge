<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;


use App\Infrastructure\Persistence\Doctrine\ORM\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity;


use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AddressFixtures extends Fixture implements DependentFixtureInterface
{
    public const ADDRESSES = [
        ['123 Tech Street', 'Paris', '75001', 'France'],
        ['45 Finance Ave', 'Lyon', '69001', 'France'],
        ['78 Green Way', 'Marseille', '13001', 'France'],
        ['9 Cloud Blvd', 'Toulouse', '31000', 'France'],
        ['56 Data Lane', 'Nice', '06000', 'France'],
        ['12 Health Rd', 'Nantes', '44000', 'France'],
        ['33 Auto Street', 'Strasbourg', '67000', 'France'],
        ['101 Retail Rd', 'Montpellier', '34000', 'France'],
        ['7 Edu St', 'Bordeaux', '33000', 'France'],
        ['88 Cyber Lane', 'Lille', '59000', 'France'],
        ['25 Media Street', 'Rennes', '35000', 'France'],
        ['14 FinTrust Ave', 'Reims', '51100', 'France'],
        ['67 SmartCity Blvd', 'Saint-Étienne', '42000', 'France'],
        ['91 AgroTech Rd', 'Toulon', '83000', 'France'],
        ['34 BioLife Lane', 'Grenoble', '38000', 'France'],
        ['77 TravelEase Street', 'Dijon', '21000', 'France'],
        ['5 LogistiX Ave', 'Angers', '49000', 'France'],
        ['66 PayFlow Rd', 'Nîmes', '30000', 'France'],
        ['23 EcoBuild Blvd', 'Villeurbanne', '69100', 'France'],
        ['88 GameForge Lane', 'Clermont-Ferrand', '63000', 'France'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ADDRESSES as $i => [$street, $city, $postalCode, $country]) {
            $address = (new AddressEntity())
                ->setStreet($street)
                ->setCity($city)
                ->setPostalCode($postalCode)
                ->setCountry($country)
                ->setUser($this->getReference('user_'.$i, UserEntity::class)); // lien avec les users existants

            $manager->persist($address);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
