<?php

namespace App\Tests\Unit\Mappers;

use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Mappers\ExpenseMapper;
use PHPUnit\Framework\TestCase;

class ExpenseMapperTest extends TestCase
{
    /** @test */
    public function shouldReturnExpectedObject()
    {
        // Arrange
        $expense = new Expense();
        $expense->id = 1;
        $expense->setValue(10);
        $expense->setDescription('Description');
        $expenseType = new ExpenseType();
        $expenseType->id = 1;
        $expenseType->setName('Entertainment');
        $expense->setExpenseType($expenseType);

        // Act
        $mappedObject = ExpenseMapper::entityToResponseDto($expense);

        // Assert
        $this->assertSame($expense->getId(), $mappedObject->id);
        $this->assertSame($expense->getDescription(), $mappedObject->description);
        $this->assertSame($expense->getValue(), $mappedObject->value);
        $this->assertSame($expense->getExpenseType()->getName(), $mappedObject->type);
    }
}
