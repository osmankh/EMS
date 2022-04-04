<?php

namespace App\Controller;

use App\Dto\CreateExpenseRequestDto;
use App\Dto\ExpenseResponseDto;
use App\Dto\UpdateExpenseRequestDto;
use App\Exceptions\NotFoundException;
use App\Mappers\ExpenseMapper;
use App\Repository\ExpenseRepository;
use App\Repository\ExpenseTypeRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ExpensesController extends AbstractController
{
    public function __construct(
        protected ExpenseRepository $repository,
        protected ExpenseTypeRepository $typeRepository,
    ) {
    }

    /**
     * List all expenses.
     *
     * @OA\Response(
     *     response=200,
     *     description="Returns a list of expenses",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=ExpenseResponseDto::class))
     *     )
     * )
     * @OA\Tag(name="Expenses")
     *
     * @return Response
     */
    #[Route('/expenses', name: 'get_expenses', methods: ['GET'])]
    public function getExpenses(): Response
    {
        $data = $this->repository->findAll();

        $data = array_map(fn ($expense): ExpenseResponseDto => ExpenseMapper::entityToResponseDto($expense), $data);

        return new JsonResponse($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Add an expense.
     *
     * @OA\Response(
     *     response=201,
     *     description="Returns created expense",
     *     @OA\JsonContent(ref=@Model(type=ExpenseResponseDto::class))
     * )
     *
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Expense type not found"
     * )
     *
     * @OA\RequestBody(
     *      description="Expense details",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="description",
     *                  description="Description of the Expense",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="value",
     *                  description="Value of the Expense",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  description="Type of the Expense, you can specifies it by the Type name of id - (eg. 'Entertainment' or 1)",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     *
     * @OA\Tag(name="Expenses")
     *
     * @param CreateExpenseRequestDto $createExpenseDto
     *
     * @return Response
     *
     * @throws InternalErrorException
     */
    #[Route('/expenses', name: 'post_expenses', methods: ['POST'])]
    public function postExpense(CreateExpenseRequestDto $createExpenseDto): Response
    {
        if (!$createExpenseDto->valid()) {
            return $createExpenseDto->validationResponse();
        }

        if (is_numeric($createExpenseDto->getType())) {
            $expenseType = $this->typeRepository->find($createExpenseDto->getType());
        } else {
            $expenseType = $this->typeRepository->findOneBy([
                'name' => $createExpenseDto->getType(),
            ]);
        }

        if ($expenseType) {
            $entity = ExpenseRepository::create($createExpenseDto->getDescription(), $createExpenseDto->getValue(), $expenseType);

            try {
                $this->repository->add($entity);
            } catch (OptimisticLockException|ORMException $e) {
                throw new InternalErrorException('Unable to add your expense', 1, $e);
            }
        } else {
            throw new NotFoundException('ExpenseType', $createExpenseDto->getType());
        }

        return new JsonResponse(ExpenseMapper::entityToResponseDto($entity), 201, ['Content-Type' => 'application/json']);
    }

    /**
     * Get Expense by id.
     *
     * @OA\Response(
     *     response=200,
     *     description="Return matched expense by id",
     *     @OA\JsonContent(ref=@Model(type=ExpenseResponseDto::class))
     * )
     *
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Requested expense id not found"
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of Expense",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Expenses")
     *
     * @param string $id
     *
     * @return Response
     */
    #[Route('/expenses/{id}', name: 'get_expense_by_id', methods: ['GET'])]
    public function getExpenseById(string $id): Response
    {
        if (!is_numeric($id)) {
            throw new BadRequestException('Expense Id must be of type int');
        }

        $expense = $this->repository->find($id);
        if (!$expense) {
            throw new NotFoundException('Expense', $id);
        }

        return new JsonResponse(ExpenseMapper::entityToResponseDto($expense), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Update Expense.
     *
     * @OA\Response(
     *     response=200,
     *     description="Return updated Expense",
     *     @OA\JsonContent(ref=@Model(type=ExpenseResponseDto::class))
     * )
     *
     * @OA\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     *
     * @OA\Response(
     *     response=404,
     *     description="Requested expense id not found"
     * )
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Id of Expense",
     *     @OA\Schema(type="integer")
     * )
     *
     * @OA\RequestBody(
     *      description="Expense fields to update",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="description",
     *                  description="Description of the Expense",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="value",
     *                  description="Value of the Expense",
     *                  type="float"
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  description="Type of the Expense, you can specifies it by the Type name of id - (eg. 'Entertainment' or 1)",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * @OA\Tag(name="Expenses")
     *
     * @param string                  $id
     * @param UpdateExpenseRequestDto $updateBody
     *
     * @return Response
     */
    #[Route('/expenses/{id}', name: 'update_expense_by_id', methods: ['PATCH'])]
    public function updateExpenseById(string $id, UpdateExpenseRequestDto $updateBody): Response
    {
        if (!is_numeric($id)) {
            throw new BadRequestException('Expense Id must be of type int');
        }

        $expense = $this->repository->find($id);
        if (!$expense) {
            throw new NotFoundException('Expense', $id);
        }

        if (!$updateBody->valid()) {
            return $updateBody->validationResponse();
        }

        $expenseType = null;

        if ($updateBody->getType()) {
            if (is_numeric($updateBody->getType())) {
                $expenseType = $this->typeRepository->find($updateBody->getType());
            } else {
                $expenseType = $this->typeRepository->findOneBy([
                    'name' => $updateBody->getType(),
                ]);
            }

            if (!$expenseType) {
                throw new NotFoundException('ExpenseType', $updateBody->getType());
            }
        }

        if ($updateBody->getDescription()) {
            $expense->setDescription($updateBody->getDescription());
        }

        if ($updateBody->getValue()) {
            $expense->setValue($updateBody->getValue());
        }

        if ($expenseType) {
            $expense->setExpenseType($expenseType);
        }

        return new JsonResponse(ExpenseMapper::entityToResponseDto($expense), 200, ['Content-Type' => 'application/json']);
    }
}
