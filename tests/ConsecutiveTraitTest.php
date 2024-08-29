<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\ConsecutiveTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversTrait(ConsecutiveTrait::class)]
class ConsecutiveTraitTest extends TestCase
{
    use ConsecutiveTrait;

    public function test()
    {
        // Create a mock object
        $mock = $this->getMockBuilder(TestClass::class)
            ->onlyMethods(['testMethod'])
            ->getMock();

        // Set up the expectation using Consecutive::create
        $mock->expects($this->exactly(2))
            ->method('testMethod')
            ->with(...$this->consecutive(['a', 1], ['b', 2]));

        // Invoke the method with the expected parameters
        $mock->testMethod('a', 1);
        $mock->testMethod('b', 2);
    }

    public function testWithMismatchedParameters()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parameters count max much in all groups');

        $this->consecutive(['a', 1], ['b']);
    }

    public function testWithNoMoreExpectedCalls()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No more expected calls');

        $mock = $this->getMockBuilder(TestClass::class)
            ->onlyMethods(['testMethod'])
            ->getMock();

        $mock->expects($this->any())
            ->method('testMethod')
            ->with(...$this->consecutive(['a', 1], ['b', 2]));

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
