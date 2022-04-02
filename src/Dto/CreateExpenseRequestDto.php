<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CreateExpenseRequestDto extends BaseRequestDto
{
    #[Type('string')]
    #[NotBlank()]
    protected string $description;

    #[Type('float')]
    #[NotBlank()]
    protected float $value;

    #[Type('string')]
    #[NotBlank()]
    protected string $type;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function autoValidateRequest(): bool
    {
        return false;
    }
}
