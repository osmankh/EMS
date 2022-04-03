<?php

namespace App\Tests\Integration\Controller;

use App\Controller\ExpensesController;
use App\DataFixtures\TestFixtures;
use App\Dto\CreateExpenseRequestDto;
use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Exceptions\NotFoundException;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExpensesControllerTest extends WebTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;
    private $container;

    /** @var ExpensesController */
    private $controller;

    /** @var Serializer */
    private $serializer;

    protected function setUp(): void
    {
        self::bootKernel();

        DatabasePrimer::prime(self::$kernel);

        $this->container = static::getContainer();

        $this->controller = $this->container->get(ExpensesController::class);

        $this->entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function getExpensesShouldReturnEmptyData(): void
    {
        $container = static::getContainer();
        /** @var ExpensesController $controller */
        $controller = $container->get(ExpensesController::class);
        $response = $controller->getExpenses();

        $this->assertEquals('[]', $response->getContent());
    }

    /** @test */
    public function getExpensesShouldReturnExpectedData(): void
    {
        // Arrange
        // Run Test DB Fixtures
        $expectedExpense = (object) [
            'description' => 'Test Description',
            'value' => 12,
        ];

        $testFixtures = new TestFixtures();
        $testFixtures->load($this->entityManager, $expectedExpense->description);

        // Act
        $response = $this->controller->getExpenses();
        $expensesResponse = json_decode($response->getContent());
        $actualExpense = $expensesResponse[0];

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($expensesResponse);
        $this->assertEquals($expectedExpense->description, $actualExpense->description);
        $this->assertEquals($expectedExpense->value, $actualExpense->value);
    }

    /** @test
     * @dataProvider postExpenseBadBodyProvider
     *
     * @throws Exception
     */
    public function postExpenseShouldReturnBadRequest(
        object $jsonBody,
    ): void {
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(ValidatorInterface::class);
        $data = json_encode($jsonBody);
        $createExpenseDto = $this->serializer->deserialize($data, CreateExpenseRequestDto::class, 'json', [
            'default_constructor_arguments' => [
                CreateExpenseRequestDto::class => ['validator' => $validator],
            ],
        ]);
        $response = $this->controller->postExpense($createExpenseDto);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function postExpenseBadBodyProvider(): array
    {
        $allData = [
            'description' => 'Test Description',
            'value' => 10,
            'type' => 'Entertainment',
        ];
        $allParamsMissing = [];
        $missingDescription = [
            ...$allData,
        ];
        unset($missingDescription['description']);
        $emptyDescription = [
            ...$allData,
            'description' => '',
        ];
        $missingValue = [
            ...$allData,
        ];
        unset($missingValue['value']);
        $stringValue = [
            ...$allData,
            'value' => 'not-valid',
        ];
        $zeroValue = [
            ...$allData,
            'value' => 0,
        ];
        $negativeValue = [
            ...$allData,
            'value' => -1,
        ];
        $missingType = [
            ...$allData,
        ];
        unset($missingType['type']);
        $emptyType = [
            ...$allData,
            'type' => '',
        ];

        return [
            'Empty Body' => [(object) $allParamsMissing],
            'Missing Description' => [(object) $missingDescription],
            'Empty Description' => [(object) $emptyDescription],
            'Missing Value' => [(object) $missingValue],
            'Value as String' => [(object) $stringValue],
            'Value as zero' => [(object) $zeroValue],
            'Negative Value' => [(object) $negativeValue],
            'Missing Type' => [(object) $missingType],
            'an Empty Type' => [(object) $emptyType],
        ];
    }

    /** @test */
    public function postExpenseShouldThrowNotFoundExceptionOnNonExistingType(): void
    {
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(ValidatorInterface::class);
        $data = json_encode((object) [
            'description' => 'Test Description',
            'value' => 10,
            'type' => 'Not Found',
        ]);
        $createExpenseDto = $this->serializer->deserialize($data, CreateExpenseRequestDto::class, 'json', [
            'default_constructor_arguments' => [
                CreateExpenseRequestDto::class => ['validator' => $validator],
            ],
        ]);
        $this->expectException(NotFoundException::class);
        $this->controller->postExpense($createExpenseDto);
    }

    /** @test */
    public function postExpenseShouldAddExpenseOnValidPayload(): void
    {
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(ValidatorInterface::class);
        $expectedExpense = [
            'description' => 'Post expense should add expense on valid payload',
            'value' => 10,
            'type' => 'Entertainment',
        ];
        $createExpenseDto = $this->serializer->deserialize(json_encode((object) $expectedExpense), CreateExpenseRequestDto::class, 'json', [
            'default_constructor_arguments' => [
                CreateExpenseRequestDto::class => ['validator' => $validator],
            ],
        ]);
        $response = $this->controller->postExpense($createExpenseDto);

        $this->assertEquals(201, $response->getStatusCode(), 'Should return 201 status code');
        $expenseRepository = $this->entityManager->getRepository(Expense::class);
        /** @var Expense $actualExpense */
        $actualExpense = $expenseRepository->findOneBy([
            'description' => $expectedExpense['description'],
        ]);
        $this->assertEquals($expectedExpense['description'], $actualExpense->getDescription(), 'Saved Expense description match payload description');
        $this->assertEquals($expectedExpense['value'], $actualExpense->getValue(), 'Saved Expense value match payload value');
        $this->assertEquals($expectedExpense['type'], $actualExpense->getExpenseType()->getName(), 'Saved Expense type match payload type');
    }

    /** @test */
    public function postExpenseShouldKnowPayloadTypeIfIdPosted(): void
    {
        // Arrange
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(ValidatorInterface::class);
        $expenseRepository = $this->entityManager->getRepository(ExpenseType::class);
        /** @var ExpenseType $expenseType */
        $expenseType = $expenseRepository->findOneBy([
            'name' => 'Entertainment',
        ]);
        $expectedExpense = [
            'description' => 'Post expense should add expense on valid payload',
            'value' => 10,
            'type' => $expenseType->getId(),
        ];
        $createExpenseDto = $this->serializer->deserialize(json_encode((object) $expectedExpense), CreateExpenseRequestDto::class, 'json', [
            'default_constructor_arguments' => [
                CreateExpenseRequestDto::class => ['validator' => $validator],
            ],
        ]);

        // Act
        $response = $this->controller->postExpense($createExpenseDto);

        // Assert
        $this->assertEquals(201, $response->getStatusCode(), 'Should return 201 status code');

        $expenseRepository = $this->entityManager->getRepository(Expense::class);
        /** @var Expense $actualExpense */
        $actualExpense = $expenseRepository->findOneBy([
            'description' => $expectedExpense['description'],
        ]);
        $this->assertEquals($expectedExpense['description'], $actualExpense->getDescription(), 'Saved Expense description match payload description');
        $this->assertEquals($expectedExpense['value'], $actualExpense->getValue(), 'Saved Expense value match payload value');
        $this->assertEquals($expenseType->getName(), $actualExpense->getExpenseType()->getName(), 'Saved Expense type match payload type');
        $this->assertEquals($expenseType->getId(), $actualExpense->getExpenseType()->getId(), 'Saved Expense type Id match payload type Id');
    }
}
