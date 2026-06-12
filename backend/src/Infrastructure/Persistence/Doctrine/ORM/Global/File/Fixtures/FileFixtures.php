<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Fixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FileFixtures extends Fixture
{
    public const IMAGE_COUNT = 50;

    private const IMAGE_NAMES = [
        'logo.png', 'office.jpg', 'team.jpg', 'workspace.jpg', 'cover.jpg',
        'profile1.jpg', 'profile2.jpg', 'profile3.jpg', 'profile4.jpg', 'profile5.jpg',
        'avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.jpg', 'avatar5.png',
        'banner1.jpg', 'banner2.jpg', 'banner3.jpg', 'banner4.jpg', 'banner5.jpg',
        'icon1.png', 'icon2.png', 'icon3.png', 'icon4.png', 'icon5.png',
        'screenshot1.png', 'screenshot2.png', 'screenshot3.png', 'screenshot4.png', 'screenshot5.png',
        'product1.jpg', 'product2.jpg', 'product3.jpg', 'product4.jpg', 'product5.jpg',
        'team1.jpg', 'team2.jpg', 'team3.jpg', 'team4.jpg', 'team5.jpg',
        'cover1.jpg', 'cover2.jpg', 'cover3.jpg', 'cover4.jpg', 'cover5.jpg',
        'office1.jpg', 'office2.jpg', 'office3.jpg', 'office4.jpg', 'office5.jpg',
    ];

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::IMAGE_COUNT; $i++) {
            $image = (new FileEntity())
                ->setName(self::IMAGE_NAMES[$i])
                ->setMime('image/jpeg')
                ->setSize((float) random_int(50_000, 3_000_000));

            $manager->persist($image);
            $this->addReference('image_'.$i, $image);
        }

        $manager->flush();
    }
}
