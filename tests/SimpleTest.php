<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        DatabasePrimer::prime(self::$kernel);

        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testItWorks()
    {
        $this->assertTrue(true);
    }
}
