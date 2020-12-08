<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests\Example;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

/**
 * @coversNothing
 */
class DeprecationStandardTest extends TestCase
{
    public function test()
    {
        if (!method_exists($this, 'expectDeprecation')) {
            $this->markTestSkipped('expectDeprecation not supported in PHPUnit v' . Version::id());
        }

        $func = function (float $a) {
            trigger_error("Use my_new_func() instead", E_USER_DEPRECATED);
            return (int)($a * 100);
        };

        $this->expectDeprecation();

        $result = $func(0.42); // <-- Will exit on deprecation

        $this->assertEquals(89, $result); // This should fail, but is never executed
    }
}
