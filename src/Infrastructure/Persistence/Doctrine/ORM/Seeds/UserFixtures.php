<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity;

use Ramsey\Uuid\Uuid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;



class UserFixtures extends Fixture
{
    public  const USER_1 = 'user_1';
    public  const USER_2 = 'user_2';

    public function load(ObjectManager $manager): void
    {
        foreach ([self::USER_1, self::USER_2] as $i => $ref) {
            $user = (new UserEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setName('Company '.$i)
                ->setEmail("company$i@test.com")
                ->setPassword(password_hash('password', PASSWORD_DEFAULT))
                ->setSiret('12345678901234')
                ->setImagePath('/logo.png');

            $manager->persist($user);
            $this->addReference($ref, $user);
        }

        $manager->flush();
    }
}
