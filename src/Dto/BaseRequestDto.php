<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseRequestDto
{
    public function __construct(protected ValidatorInterface $validator)
    {
        $this->populate();
    }

    public function valid(): bool
    {
        $messages = $this->validate();

        if (count($messages['errors']) > 0) {
            return false;
        }

        return true;
    }

    public function validationResponse(): JsonResponse
    {
        $messages = $this->validate();

        return new JsonResponse($messages, 400);
    }

    private function validate()
    {
        $errors = $this->validator->validate($this);

        $messages = ['message' => 'validation_failed', 'errors' => []];

        /* @var ConstraintViolation */
        foreach ($errors as $message) {
            $messages['errors'][] = [
                'property' => $message->getPropertyPath(),
                'value' => $message->getInvalidValue(),
                'message' => $message->getMessage(),
            ];
        }

        return $messages;
    }

    public function getRequest(): Request
    {
        return Request::createFromGlobals();
    }

    protected function populate(): void
    {
        try {
            foreach ($this->getRequest()->toArray() as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->{$property} = $value;
                }
            }
        } catch (\Exception) {

        }
    }

    protected function autoValidateRequest(): bool
    {
        return true;
    }
}
