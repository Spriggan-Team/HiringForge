<?php

namespace App\Tests\Integration\Repositories;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class CandidateRepositoryTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
    }
}

