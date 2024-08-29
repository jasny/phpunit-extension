<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\InContextOfTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(InContextOfTrait::class)]
class InContextOfTraitTest extends TestCase
{
    use InContextOfTrait;

    public function test()
    {
        $object = new class {
            private $privateProp = "foo";
            private function privateMethod($planet) { return "hello $planet"; }
        };
        
        $result = $this->inContextOf($object, fn() => [
            $object->privateProp,
            $object->privateMethod("world")
        ]);
        
        $this->assertEquals(["foo", "hello world"], $result);
    }
    
    public function testThis()
    {
        $object = new class {
            private $privateProp = "foo";
        };
        
        $this->inContextOf($object, fn() => $this->assertEquals('foo', $object->privateProp));
    }
}

