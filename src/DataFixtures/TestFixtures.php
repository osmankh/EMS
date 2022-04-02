<?php

namespace App\DataFixtures;

use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager, string $description = 'Test Expense'): void
    {
        self::seedExpenses($manager, $description);
    }

    private static function seedExpenses(ObjectManager $manager, string $description)
    {
        $expense = new Expense();
        $expense->setDescription($description);
        $expense->setValue(12);

        $expenseTypeRepo = $manager->getRepository(ExpenseType::class);
        $type = $expenseTypeRepo->findOneBy([
            'name' => ExpenseTypeEnum::ENTERTAINMENT,
        ]);
        $expense->setExpenseType($type);

        $manager->persist($expense);
        $manager->flush();
    }
}
