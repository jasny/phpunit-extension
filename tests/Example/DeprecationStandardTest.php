<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests\Example;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class DeprecationStandardTest extends TestCase
{
    public function test()
    {
        $func = function (float $a) {
            trigger_error("Use my_new_func() instead", E_USER_DEPRECATED);
            return (int)($a * 100);
        };

        $this->expectDeprecation();

        $result = $func(0.42); // <-- Will exit on deprecation

        $this->assertEquals(89, $result); // This should fail, but is never executed
    }
}
