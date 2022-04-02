<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
class Expense
{
    /**
     * @var int
     * @OA\Property(description="The unique identifier of the Expense.")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    /**
     * @var string
     * @OA\Property(description="The description of the Expense.")
     */
    #[ORM\Column(type: 'string', length: 255)]
    public $description;

    /**
     * @var float
     * @OA\Property(description="The Value of the Expense.")
     */
    #[ORM\Column(type: 'float')]
    public $value;

    /**
     * @var int
     * @OA\Property(description="The Type of the Expense.")
     */
    #[ORM\ManyToOne(targetEntity: ExpenseType::class, inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    public $expenseType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getExpenseType(): ?ExpenseType
    {
        return $this->expenseType;
    }

    public function setExpenseType(?ExpenseType $expenseType): self
    {
        $this->expenseType = $expenseType;

        return $this;
    }
}
