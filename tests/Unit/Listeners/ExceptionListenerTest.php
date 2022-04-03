<?php

namespace App\Tests\Unit\Listeners;

use App\Exceptions\NotFoundException;
use App\Listeners\ExceptionListener;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListenerTest extends KernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    private function getEvent(\Throwable $e): ExceptionEvent
    {
        // Arrange
        $request = $this->createMock(Request::class);
        $logger = $this->createMock(LoggerInterface::class);

        $exceptionEventMock = new ExceptionEvent(self::$kernel, $request, 1, $e);

        $exceptionListener = new ExceptionListener();
        $exceptionListener->setLogger($logger);
        $exceptionListener->onKernelException($exceptionEventMock);

        return $exceptionEventMock;
    }

    /** @test */
    public function shouldReturn404StatusCodeOnNotFoundException()
    {
        // Arrange | Act
        $exceptionEventMock = $this->getEvent(new NotFoundException('Expense', 1));

        // Assert
        $actual = $exceptionEventMock->getResponse()->getStatusCode();
        $this->assertEquals(404, $actual);
    }

    /** @test */
    public function shouldReturnExpectedStatusCodeOnHttpException()
    {
        // Arrange
        $expected = 400;

        // Act
        $exceptionEventMock = $this->getEvent(new HttpException($expected, 'Error Message'));

        // Assert
        $actual = $exceptionEventMock->getResponse()->getStatusCode();
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function shouldReturn500StatusCodeOnUnknownException()
    {
        // Arrange
        $expected = 500;

        // Act
        $exceptionEventMock = $this->getEvent(new ORMException('Error Message'));

        // Assert
        $actual = $exceptionEventMock->getResponse()->getStatusCode();
        $this->assertEquals($expected, $actual);
    }
}
