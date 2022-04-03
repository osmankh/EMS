<?php

namespace App\Exceptions;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $entityName, int|string $id)
    {
        parent::__construct("$entityName #$id was not found");
    }
}
