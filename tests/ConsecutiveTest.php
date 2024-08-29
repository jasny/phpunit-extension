<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\Consecutive;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Consecutive::class)]
class ConsecutiveTest extends TestCase
{
    public function testCreate()
    {
        // Create a mock object
        $mock = $this->getMockBuilder(TestClass::class)
            ->onlyMethods(['testMethod'])
            ->getMock();

        // Set up the expectation using Consecutive::create
        $mock->expects($this->exactly(2))
            ->method('testMethod')
            ->with(...Consecutive::create(['a', 1], ['b', 2]));

        // Invoke the method with the expected parameters
        $mock->testMethod('a', 1);
        $mock->testMethod('b', 2);
    }

    public function testCreateWithMismatchedParameters()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parameters count max much in all groups');

        Consecutive::create(['a', 1], ['b']);
    }

    public function testCreateWithNoMoreExpectedCalls()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No more expected calls');

        $mock = $this->getMockBuilder(TestClass::class)
            ->onlyMethods(['testMethod'])
            ->getMock();

        $mock->expects($this->any())
            ->method('testMethod')
            ->with(...Consecutive::create(['a', 1], ['b', 2]));

        $mock->testMethod('a', 1);
        $mock->testMethod('b', 2);
        $mock->testMethod('c', 3); // This should trigger the exception
    }
}

class TestClass
{
    public function testMethod($param1, $param2)
    {
        // Method to be mocked
    }
}
