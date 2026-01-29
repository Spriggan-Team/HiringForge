<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Seeds;

use App\Infrastructure\Persistence\Doctrine\ORM\ImageEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture
{
    public const IMAGE_COUNT = 50;

    private const IMAGE_NAMES = [
        'logo.png', 'office.jpg', 'team.jpg', 'workspace.jpg', 'cover.jpg',
        'profile1.jpg', 'profile2.jpg', 'profile3.jpg', 'profile4.jpg', 'profile5.jpg',
        'avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png', 'avatar5.png',
        'banner1.jpg', 'banner2.jpg', 'banner3.jpg', 'banner4.jpg', 'banner5.jpg',
        'icon1.png', 'icon2.png', 'icon3.png', 'icon4.png', 'icon5.png',
        'screenshot1.png', 'screenshot2.png', 'screenshot3.png', 'screenshot4.png', 'screenshot5.png',
        'product1.jpg', 'product2.jpg', 'product3.jpg', 'product4.jpg', 'product5.jpg',
        'team1.jpg', 'team2.jpg', 'team3.jpg', 'team4.jpg', 'team5.jpg',
    ];

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::IMAGE_COUNT; $i++) {
            $image = (new ImageEntity())
                ->setOriginalName(self::IMAGE_NAMES[$i])
                ->setMime('image/jpeg')
                ->setSize((float) random_int(50_000, 3_000_000));

            $manager->persist($image);
            $this->addReference('image_'.$i, $image);
        }

        $manager->flush();
    }
}
