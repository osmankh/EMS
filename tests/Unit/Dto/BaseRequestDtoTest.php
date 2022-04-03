<?php

namespace App\Tests\Unit\Dto;

use App\Dto\BaseRequestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TestBaseRequest extends BaseRequestDto
{
}

class BaseRequestDtoTest extends TestCase
{
    protected $testBaseRequest;

    public function setUp(): void
    {
        parent::setUp();

        // Arrange
        $validatorMock = $this->createMock(ValidatorInterface::class);
        $constraintViolationList = new ConstraintViolationList();
        $constraintViolationList->add(new ConstraintViolation('Error', '', [], '', '', ''));

        $validatorMock
            ->method('validate')
            ->willReturn($constraintViolationList);

        $this->testBaseRequest = new TestBaseRequest($validatorMock);
    }

    /** @test */
    public function validShouldReturnFalseOnError()
    {
        // Act
        $isValid = $this->testBaseRequest->valid();

        // Assert
        $this->assertEquals(false, $isValid);
    }

    /** @test */
    public function validateShouldReturnErrorsArrayOnFailure()
    {
        // Act
        $errors = $this->testBaseRequest->validate();

        // Assert
        $this->assertArrayHasKey('errors', $errors);
        $this->assertCount(1, $errors['errors']);
        $this->assertEquals('validation_failed', $errors['message']);
        $this->assertArrayHasKey('message', $errors);
    }

    /** @test */
    public function validateResponseShouldReturnJsonResponseContainingTheError()
    {
        // Act
        $response = $this->testBaseRequest->validationResponse();

        // Assert
        $this->assertEquals(400, $response->getStatusCode());
    }
}
