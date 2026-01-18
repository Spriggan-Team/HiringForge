<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\CandidateEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;



class CandidateFixtures extends Fixture
{
    public const CANDIDATES = [
        ['Lucas', 'Martin'],
        ['Emma', 'Durand'],
        ['Hugo', 'Bernard'],
        ['Chloé', 'Petit'],
        ['Thomas', 'Moreau'],
        ['Léa', 'Robert'],
        ['Maxime', 'Richard'],
        ['Sarah', 'Dubois'],
        ['Nicolas', 'Fournier'],
        ['Camille', 'Girard'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CANDIDATES as $i => [$first, $last]) {
            $candidate = (new CandidateEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setFirstName($first)
                ->setLastName($last)
                ->setEmail(strtolower("$first.$last@test.com"))
                ->setPassword(password_hash('password', PASSWORD_DEFAULT))
                ->setImageFilePath('/avatars/'.$first.'.png')
                ->setCVFilePath('/cvs/'.$first.'.pdf');

            $manager->persist($candidate);
            $this->addReference('candidate_'.$i, $candidate);
        }

        $manager->flush();
    }
}

