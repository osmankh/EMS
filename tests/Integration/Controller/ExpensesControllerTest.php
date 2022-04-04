<?php

namespace App\Tests\Integration\Controller;

use App\Controller\ExpensesController;
use App\DataFixtures\TestFixtures;
use App\Dto\CreateExpenseRequestDto;
use App\Entity\Expense;
use App\Entity\ExpenseType;
use App\Enums\ExpenseTypeEnum;
use App\Exceptions\NotFoundException;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExpensesControllerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ContainerInterface $container;

    /** @var ExpensesController */
    private $controller;

    private Serializer $serializer;

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
    }

    /** @test */
    public function getExpensesShouldReturnEmptyData(): void
    {
        // Arrange
        $container = static::getContainer();
        /** @var ExpensesController $controller */
        $controller = $container->get(ExpensesController::class);

        // Act
        $response = $controller->getExpenses();

        // Assert
        $this->assertSame('[]', $response->getContent());
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
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($expensesResponse);
        $this->assertSame($expectedExpense->description, $actualExpense->description);
        $this->assertSame($expectedExpense->value, $actualExpense->value);
    }

    /** @test
     * @dataProvider postExpenseBadBodyProvider
     *
     * @throws Exception
     */
    public function postExpenseShouldReturnBadRequest(
        object $jsonBody,
    ): void {
        // Arrange
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(ValidatorInterface::class);
        $data = json_encode($jsonBody);
        $createExpenseDto = $this->serializer->deserialize($data, CreateExpenseRequestDto::class, 'json', [
            'default_constructor_arguments' => [
                CreateExpenseRequestDto::class => ['validator' => $validator],
            ],
        ]);

        // Act
        $response = $this->controller->postExpense($createExpenseDto);

        // Assert
        $this->assertSame(400, $response->getStatusCode());
    }

    #[ArrayShape(
        [
            'Empty Body' => 'object[]',
            'Missing Description' => 'object[]',
            'Empty Description' => 'object[]',
            'Missing Value' => 'object[]',
            'Value as String' => 'object[]',
            'Value as zero' => 'object[]',
            'Negative Value' => 'object[]',
            'Missing Type' => 'object[]',
            'an Empty Type' => 'object[]',
        ]
    )]
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

    /** @test
     * @throws InternalErrorException
     */
    public function postExpenseShouldThrowNotFoundExceptionOnNonExistingType(): void
    {
        // Arrange
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

        // Act | Assert
        $this->expectException(NotFoundException::class);
        $this->controller->postExpense($createExpenseDto);
    }

    /** @test
     * @throws InternalErrorException
     */
    public function postExpenseShouldAddExpenseOnValidPayload(): void
    {
        // Arrange
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

        // Act
        $response = $this->controller->postExpense($createExpenseDto);

        // Assert
        $this->assertSame(201, $response->getStatusCode(), 'Should return 201 status code');
        $expenseRepository = $this->entityManager->getRepository(Expense::class);
        /** @var Expense $actualExpense */
        $actualExpense = $expenseRepository->findOneBy([
            'description' => $expectedExpense['description'],
        ]);
        $this->assertSame($expectedExpense['description'], $actualExpense->getDescription(), 'Saved Expense description match payload description');
        $this->assertEquals($expectedExpense['value'], $actualExpense->getValue(), 'Saved Expense value match payload value');
        $this->assertSame($expectedExpense['type'], $actualExpense->getExpenseType()->getName(), 'Saved Expense type match payload type');
    }

    /** @test
     * @throws InternalErrorException
     */
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
        $this->assertSame(201, $response->getStatusCode(), 'Should return 201 status code');

        $expenseRepository = $this->entityManager->getRepository(Expense::class);
        /** @var Expense $actualExpense */
        $actualExpense = $expenseRepository->findOneBy([
            'description' => $expectedExpense['description'],
        ]);
        $this->assertSame($expectedExpense['description'], $actualExpense->getDescription(), 'Saved Expense description match payload description');
        $this->assertEquals($expectedExpense['value'], $actualExpense->getValue(), 'Saved Expense value match payload value');
        $this->assertSame($expenseType->getName(), $actualExpense->getExpenseType()->getName(), 'Saved Expense type match payload type');
        $this->assertSame($expenseType->getId(), $actualExpense->getExpenseType()->getId(), 'Saved Expense type Id match payload type Id');
    }

    /** @test */
    public function getExpenseByIdShouldReturnABadRequestOnInvalidIdType(): void
    {
        // Assert | Act
        $this->expectException(BadRequestException::class);
        $this->controller->getExpenseById('bad-string-id');
    }

    /** @test */
    public function getExpenseByIdShouldThrowNotFoundExceptionOnNonExistingExpense(): void
    {
        $this->expectException(NotFoundException::class);
        $this->controller->getExpenseById(10);
    }

    /** @test */
    public function getExpenseByIdShouldReturnExpectedExpense(): void
    {
        // Arrange
        // Run Test DB Fixtures
        $expectedExpense = (object) [
            'description' => 'Test Description',
            'value' => 12,
            'type' => ExpenseTypeEnum::ENTERTAINMENT,
        ];

        $testFixtures = new TestFixtures();
        $testFixtures->load(
            $this->entityManager,
            $expectedExpense->description,
            $expectedExpense->value,
            $expectedExpense->type,
        );

        $response = $this->controller->getExpenseById(1);

        $actual = json_decode($response->getContent());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $actual->id);
        $this->assertSame($expectedExpense->description, $actual->description);
        $this->assertSame($expectedExpense->value, $actual->value);
        $this->assertSame($expectedExpense->type, $actual->type);
    }
}
