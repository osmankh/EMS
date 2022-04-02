<?php

namespace App\Controller;

use App\Dto\CreateExpenseRequestDto;
use App\Entity\Expense;
use App\Exceptions\NotFoundException;
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
     *        @OA\Items(ref=@Model(type=Expense::class, groups={"full"}))
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

        return new JsonResponse($data, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Add an expense.
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
        $createExpenseDto->validate();

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

        return new JsonResponse([], 201, ['Content-Type' => 'application/json']);
    }
}
