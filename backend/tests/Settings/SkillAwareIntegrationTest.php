<?php

namespace App\Tests\Settings;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;


abstract class SkillAwareIntegrationTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->loadSkillReference();
    }

    private function loadSkillReference(): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()
            ->get(EntityManagerInterface::class);

        $path = self::$kernel->getProjectDir()
            . '/tests/Fixtures/Reference/skills.sql';

        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new \RuntimeException('Skills fixture not found');
        }

        $em->getConnection()
            ->executeStatement($sql);
    }
}