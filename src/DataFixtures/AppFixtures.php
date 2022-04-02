<?php

namespace App\DataFixtures;

use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
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
            ExpenseTypeEnum::ENTERTAINMENT,
            ExpenseTypeEnum::FOOD,
            ExpenseTypeEnum::BILLS,
            ExpenseTypeEnum::TRANSPORT,
            ExpenseTypeEnum::OTHER,
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
