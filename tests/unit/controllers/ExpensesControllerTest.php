<?php

namespace App\Tests\unit\controllers;

use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExpensesControllerTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        DatabasePrimer::prime(self::$kernel);

        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    /** @test */
    public function shouldBeCalledAsExpected()
    {
        $this->assertTrue(true);
    }
}
