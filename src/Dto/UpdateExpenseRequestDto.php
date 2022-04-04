<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateExpenseRequestDto extends BaseRequestDto
{
    #[Assert\Type('string')]
    public $description;

    #[Assert\Type(
        type: [
            'float',
            'integer',
        ]
    )]
    #[Assert\Positive()]
    public $value;

    #[Assert\Type(
        type: [
            'string',
            'integer',
        ],
        message: 'The value {{ value }} is not a valid {{ type }} name or id',
    )]
    public $type;

    public function getDescription(): null|string
    {
        return $this->description;
    }

    public function getValue(): null|string
    {
        return $this->value;
    }

    public function getType(): null|string
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
