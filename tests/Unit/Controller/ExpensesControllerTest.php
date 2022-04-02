<?php

namespace App\Tests\Unit\Controller;

use App\Controller\ExpensesController;
use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use App\Repository\ExpenseRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ExpensesControllerTest extends TestCase
{
    /** @test */
    public function getExpensesShouldReturnExpectedResponse(): void
    {
        $expenseMock = new Expense();
        $expenseMock->setDescription('Expense Mock');
        $expenseMock->setValue(10);

        $expenseTypeMock = new ExpenseType();
        $expenseTypeMock->setName(ExpenseTypeEnum::ENTERTAINMENT);
        $expenseMock->setExpenseType($expenseTypeMock);

        /** @var ExpenseRepository $expenseRepositoryMock */
        $expenseRepositoryMock = $this->createMock(ExpenseRepository::class);
        $expenseRepositoryMock
            ->method('findAll')
            ->willReturn([$expenseMock]);

        $controller = new ExpensesController($expenseRepositoryMock);

        $container = $this->createMock(ContainerInterface::class);
        $controller->setContainer($container);

        $this->assertEquals(json_encode([$expenseMock]), $controller->getExpenses()->getContent());
    }
}
