<?php

namespace App\Enums;

class ExpenseTypeEnum
{
    public const __default = self::ENTERTAINMENT;

    public const ENTERTAINMENT = 'Entertainment';
    public const FOOD = 'Food';
    public const BILLS = 'Bills';
    public const TRANSPORT = 'Transport';
    public const OTHER = 'Other';
}
