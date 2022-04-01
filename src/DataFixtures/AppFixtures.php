<?php

namespace App\DataFixtures;

use App\Entity\ExpenseType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        self::seedExpenses($manager);
    }

    private static function seedExpenses(ObjectManager $manager)
    {
        $expenseTypes = [
            'Entertainment',
            'Food',
            'Bills',
            'Transport',
            'Other',
        ];

        foreach ($expenseTypes as $expenseType) {
            $manager->persist(self::createExpenseType($expenseType));
        }

        $manager->flush();
    }

    private static function createExpenseType(string $name): ExpenseType
    {
        $expenseType = new ExpenseType();
        $expenseType->setName($name);

        return $expenseType;
    }
}
