<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\CandidateEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CandidateFixtures extends Fixture
{
    public const CANDIDATE_1 = 'candidate_1';
    public const CANDIDATE_2 = 'candidate_2';

    public function load(ObjectManager $manager): void
    {
        foreach ([self::CANDIDATE_1, self::CANDIDATE_2] as $i => $ref) {
            $candidate = (new CandidateEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setFirstName('John'.$i)
                ->setLastName('Doe'.$i)
                ->setEmail("candidate$i@test.com")
                ->setPassword(password_hash('password', PASSWORD_DEFAULT))
                ->setImageFilePath('/avatar.png')
                ->setCVFilePath('/cv.pdf');

            $manager->persist($candidate);
            $this->addReference($ref, $candidate);
        }

        $manager->flush();
    }
}
