<?php

namespace App\Tests\Integration\Controller;

use App\Controller\ExpensesController;
use App\DataFixtures\TestFixtures;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExpensesControllerTest extends WebTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        DatabasePrimer::prime(self::$kernel);

        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function getExpensesShouldReturnEmptyData(): void
    {
        $container = static::getContainer();
        /** @var ExpensesController $controller */
        $controller = $container->get(ExpensesController::class);
        $response = $controller->getExpenses();

        $this->assertEquals('[]', $response->getContent());
    }

    /** @test */
    public function getExpensesShouldReturnExpectedData(): void
    {
        // Arrange
        // Run Test DB Fixtures
        $expectedExpense = (object) [
            'description' => 'Test Description',
            'value' => 12,
        ];

        $testFixtures = new TestFixtures();
        $testFixtures->load($this->entityManager, $expectedExpense->description);

        $container = static::getContainer();
        /** @var ExpensesController $controller */
        $controller = $container->get(ExpensesController::class);

        // Act
        $response = $controller->getExpenses();
        $expensesResponse = json_decode($response->getContent());
        $actualExpense = $expensesResponse[0];

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($expensesResponse);
        $this->assertEquals($expectedExpense->description, $actualExpense->description);
        $this->assertEquals($expectedExpense->value, $actualExpense->value);
    }
}
