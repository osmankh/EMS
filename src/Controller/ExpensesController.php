<?php

namespace App\Controller;

use App\Dto\CreateExpenseRequestDto;
use App\Dto\ExpenseResponseDto;
use App\Entity\Expense;
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
     * @OA\Tag(name="expenses")
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
     * @OA\Tag(name="expenses")
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
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="expenses")
     *
     * @param int $id
     *
     * @return Response
     */
    #[Route('/expenses/{id}', name: 'get_expense_by_id', methods: ['GET'])]
    public function getExpenseById(int $id): Response
    {
        return new JsonResponse([], 200, ['Content-Type' => 'application/json']);
    }
}
