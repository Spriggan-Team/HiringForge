<?php

namespace App\Tests\Integration\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
     protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
    }

    #[Test]
    public function it_works(): void
    {
        self::markTestIncomplete('Test à implémenter');
    }
}