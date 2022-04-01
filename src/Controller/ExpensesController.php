<?php

namespace App\Controller;

use App\Repository\ExpenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpensesController extends AbstractController
{
    #[Route('/expenses', name: 'app_expenses')]
    public function getExpenses(ExpenseRepository $repository): Response
    {
        return $this->json($repository->findAll());
    }
}
