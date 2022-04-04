<?php

namespace App\DataFixtures;

use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TestFixtures extends Fixture
{
    public function load(
        ObjectManager $manager,
        string $description = 'Test Expense',
        int $value = 12,
        string $type = ExpenseTypeEnum::ENTERTAINMENT
    ): void {
        self::seedExpenses($manager, $description, $value, $type);
    }

    private static function seedExpenses(
        ObjectManager $manager,
        string $description,
        int $value,
        string $typeName
    ) {
        $expense = new Expense();
        $expense->setDescription($description);
        $expense->setValue($value);

        $expenseTypeRepo = $manager->getRepository(ExpenseType::class);
        $type = $expenseTypeRepo->findOneBy([
            'name' => $typeName,
        ]);
        $expense->setExpenseType($type);

        $manager->persist($expense);
        $manager->flush();
    }
}
