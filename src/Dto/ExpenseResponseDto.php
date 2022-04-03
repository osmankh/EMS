<?php

namespace App\Dto;

use OpenApi\Annotations as OA;

class ExpenseResponseDto
{
    /**
     * @OA\Property(description="The unique identifier of the Expense.")
     */
    public int $id;

    /**
     * @OA\Property(description="The description of the Expense.")
     */
    public string $description;

    /**
     * @OA\Property(description="The Value of the Expense.")
     */
    public float $value;

    /**
     * @OA\Property(description="The Type of the Expense.")
     */
    public string $type;

    public function __construct(
        int $id,
        string $description,
        float $value,
        string $type
    ) {
    }
}
