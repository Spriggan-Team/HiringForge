<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Fixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
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
            $image = new FileEntity();
            $image->setOriginalName('/avatars/'. time() .$first.'.png')
                  ->setSize(random_int(154, 1202477.0))
                  ->setMime("image/jpeg");
            
            $cv = new FileEntity();
            $cv->setOriginalName('/cvs/'. time() .$first.'.pdf')
                  ->setSize(random_int(154, 1202477.0))
                  ->setMime("audio/webm");
            
            $candidate = (new CandidateEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setFirstName($first)
                ->setLastName($last)
                ->setEmail(strtolower("$first.$last@test.com"))
                ->setPassword(password_hash('password', PASSWORD_DEFAULT))
                ->attachImage($image)
                ->attachCV($cv);

            $manager->persist($candidate);
            $this->addReference('candidate_'.$i, $candidate);
        }

        $manager->flush();
    }
}

