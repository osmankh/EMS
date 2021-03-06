<?php

namespace App\Tests;

use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExpenseTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        DatabasePrimer::prime(self::$kernel);

        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function shouldHaveFiveExpenseTypesFixturesFromStart()
    {
        // Arrange
        $expenseTypeRepository = $this->entityManager->getRepository(ExpenseType::class);

        $expenseTypes = [
            ExpenseTypeEnum::ENTERTAINMENT,
            ExpenseTypeEnum::FOOD,
            ExpenseTypeEnum::BILLS,
            ExpenseTypeEnum::TRANSPORT,
            ExpenseTypeEnum::OTHER,
        ];

        foreach ($expenseTypes as $expenseType) {
            // Act
            /** @var ExpenseType $expenseTypeRecord */
            $expenseTypeRecord = $expenseTypeRepository->findOneBy(['name' => $expenseType]);

            // Assert
            $this->assertNotEmpty($expenseTypeRecord);
        }

        // Act
        $allExpenseTypeCount = $expenseTypeRepository->count([]);

        // Assert
        $this->assertSame(count($expenseTypes), $allExpenseTypeCount);
    }

    /** @test */
    public function anExpenseCanBeCreatedInTheDatabase()
    {
        // Arrange
        $expenseTypeRepository = $this->entityManager->getRepository(ExpenseType::class);

        /** @var ExpenseType $expenseTypeRecord */
        $expenseTypeRecord = $expenseTypeRepository->findOneBy(['name' => ExpenseTypeEnum::ENTERTAINMENT]);

        $expenseDescription = 'Simple expense';
        $expenseValue = 12.2;

        $expense = new Expense();
        $expense->setDescription($expenseDescription);
        $expense->setValue($expenseValue);
        $expense->setExpenseType($expenseTypeRecord);

        // Act
        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $expenseRepository = $this->entityManager->getRepository(Expense::class);

        /** @var Expense $expenseRecord */
        $expenseRecord = $expenseRepository->findOneBy([
            'description' => $expenseDescription,
            'value' => $expenseValue,
        ]);

        // Assert
        $this->assertSame($expenseDescription, $expenseRecord->getDescription());
        $this->assertSame($expenseValue, $expenseRecord->getValue());
        $this->assertSame(ExpenseTypeEnum::ENTERTAINMENT, $expenseRecord->getExpenseType()->getName());
    }
}
