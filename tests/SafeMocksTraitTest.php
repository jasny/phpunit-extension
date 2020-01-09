<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\SafeMocksTrait;
use Jasny\PHPUnit\Tests\Support\Dummy;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\PHPUnit\SafeMocksTrait
 */
class SafeMocksTraitTest extends TestCase
{
    use SafeMocksTrait;

    public function test()
    {
        $mock = $this->createMock(Dummy::class);
        $mock->expects($this->once())->method('hello');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Return value inference disabled and no expectation set up for '
            . Dummy::class . '::bye()');

        $mock->hello();
        $mock->bye();
    }
}
