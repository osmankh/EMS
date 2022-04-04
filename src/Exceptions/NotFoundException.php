<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
    public function __construct(string $entityName, int|string $id)
    {
        parent::__construct("$entityName #$id was not found");
    }
}
