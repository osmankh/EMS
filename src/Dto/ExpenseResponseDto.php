<?php

namespace App\Dto;

class ExpenseResponseDto
{
    public function __construct(
        public int $id,
        public string $description,
        public float $value,
        public string $type)
    {
    }
}
