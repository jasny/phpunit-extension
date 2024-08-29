<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests\Example;

use Jasny\PHPUnit\ExpectWarningTrait;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class DeprecationContinueTest extends TestCase
{
    use ExpectWarningTrait;

    public function testFoo(): void
    {
        $func = function (float $a) {
            trigger_error("Use my_new_func() instead", E_USER_DEPRECATED);
            return (int)($a * 100);
        };

        $this->expectDeprecation();

        $result = $func(0.42);

        try {
            $this->assertEquals(89, $result);
        } catch (AssertionFailedError $exception) {
            $this->assertEquals("Failed asserting that 42 matches expected 89.", $exception->getMessage());
        }
    }
}
