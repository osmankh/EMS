<?php

namespace App\Mappers;

use App\Dto\ExpenseResponseDto;
use App\Entity\Expense;

class ExpenseMapper
{
    public static function entityToResponseDto(Expense $expense): ExpenseResponseDto
    {
        return new ExpenseResponseDto(
            $expense->getId(),
            $expense->getDescription(),
            $expense->getValue(),
            $expense->getExpenseType()->getName(),
        );
    }
}
