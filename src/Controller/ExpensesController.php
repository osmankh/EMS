<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Repository\ExpenseRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpensesController extends AbstractController
{
    public function __construct(protected ExpenseRepository $repository)
    {
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
     */
    #[Route('/api/expenses', name: 'app_expenses', methods: ['GET', 'HEAD'])]
    public function getExpenses(): Response
    {
        return $this->json($this->repository->findAll());
    }
}
