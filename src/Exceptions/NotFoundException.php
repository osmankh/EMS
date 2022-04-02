<?php

namespace App\Exceptions;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $entityName, int $id)
    {
        parent::__construct("$entityName #$id was not found");
    }
}
