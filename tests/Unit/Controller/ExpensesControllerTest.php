<?php

namespace App\Tests\Unit\Controller;

use App\Controller\ExpensesController;
use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ExpensesControllerTest extends TestCase
{
    protected ExpensesController $controller;
    protected Expense $expenseMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->expenseMock = new Expense();
        $this->expenseMock->id = 1;
        $this->expenseMock->setDescription('Expense Mock');
        $this->expenseMock->setValue(10);

        $expenseTypeMock = new ExpenseType();
        $expenseTypeMock->setName(ExpenseTypeEnum::ENTERTAINMENT);
        $this->expenseMock->setExpenseType($expenseTypeMock);

        /** @var ExpenseRepository $expenseRepositoryMock */
        $expenseRepositoryMock = $this->createMock(ExpenseRepository::class);
        $expenseRepositoryMock
            ->method('findAll')
            ->willReturn([$this->expenseMock]);

        /** @var ExpenseTypeRepository $expenseTypeRepositoryMock */
        $expenseTypeRepositoryMock = $this->createMock(ExpenseTypeRepository::class);

        $this->controller = new ExpensesController($expenseRepositoryMock, $expenseTypeRepositoryMock);
    }

    /** @test */
    public function getExpensesShouldReturnExpectedResponse(): void
    {
        // Arrange
        $expected = (object) [
            'id' => $this->expenseMock->getId(),
            'description' => $this->expenseMock->getDescription(),
            'value' => $this->expenseMock->getValue(),
            'type' => $this->expenseMock->getExpenseType()->getName(),
        ];

        $container = $this->createMock(ContainerInterface::class);
        $this->controller->setContainer($container);

        // Act
        $content = $this->controller->getExpenses()->getContent();

        // Assert
        $this->assertEquals(json_encode([$expected]), $content);
    }
}
