<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateExpenseRequestDto extends BaseRequestDto
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    protected $description;

    #[Assert\Type([
        'float',
        'integer',
    ])]
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    protected $value;

    #[Assert\Type(
        type: [
            'string',
            'integer',
        ],
        message: 'The value {{ value }} is not a valid {{ type }} name or id',
    )]
    #[Assert\NotBlank()]
    protected $type;

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

    /**
     * @param $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @param $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @param $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }
}
