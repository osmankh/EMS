<?php

namespace App\Tests\unit\Entity;

use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use App\Tests\DatabasePrimer;
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
        $expenseTypeRepository = $this->entityManager->getRepository(ExpenseType::class);

        $expenseTypes = [
            ExpenseTypeEnum::ENTERTAINMENT,
            ExpenseTypeEnum::FOOD,
            ExpenseTypeEnum::BILLS,
            ExpenseTypeEnum::TRANSPORT,
            ExpenseTypeEnum::OTHER,
        ];

        foreach ($expenseTypes as $expenseType) {
            /** @var ExpenseType $expenseTypeRecord */
            $expenseTypeRecord = $expenseTypeRepository->findOneBy(['name' => $expenseType]);
            $this->assertNotEmpty($expenseTypeRecord);
        }

        $allExpenseTypeCount = $expenseTypeRepository->count([]);
        $this->assertEquals(count($expenseTypes), $allExpenseTypeCount);
    }

    /** @test */
    public function anExpenseCanBeCreatedInTheDatabase()
    {
        $expenseTypeRepository = $this->entityManager->getRepository(ExpenseType::class);

        /** @var ExpenseType $expenseTypeRecord */
        $expenseTypeRecord = $expenseTypeRepository->findOneBy(['name' => ExpenseTypeEnum::ENTERTAINMENT]);

        $expenseDescription = 'Simple expense';
        $expenseValue = 12.2;

        $expense = new Expense();
        $expense->setDescription($expenseDescription);
        $expense->setValue($expenseValue);
        $expense->setExpenseType($expenseTypeRecord);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        $expenseRepository = $this->entityManager->getRepository(Expense::class);

        /** @var Expense $expenseRecord */
        $expenseRecord = $expenseRepository->findOneBy([
            'description' => $expenseDescription,
            'value' => $expenseValue,
        ]);

        $this->assertEquals($expenseDescription, $expenseRecord->getDescription());
        $this->assertEquals($expenseValue, $expenseRecord->getValue());
        $this->assertEquals(ExpenseTypeEnum::ENTERTAINMENT, $expenseRecord->getExpenseType()->getName());
    }
}
