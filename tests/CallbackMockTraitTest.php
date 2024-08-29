<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\ExpectationFailedException;

#[CoversTrait(CallbackMockTrait::class)]
class CallbackMockTraitTest extends TestCase
{
    use CallbackMockTrait;

    protected function forgetMockObjects(): void
    {
        $refl = new \ReflectionProperty(TestCase::class, 'mockObjects');
        $refl->setAccessible(true);
        
        $refl->setValue($this, []);
    }

    public function testCreateCallbackMock(): void
    {
        $callback = $this->createCallbackMock($this->any());
        
        $this->assertTrue(is_callable($callback));
        
        $this->assertNull($callback('foo'));
        $this->assertNull($callback('bar'));
        $this->assertNull($callback('zoo'));
    }
    
    public function testCreateCallbackMockNeverInvoke(): void
    {
        $callback = $this->createCallbackMock($this->never());
        
        $this->assertTrue(is_callable($callback));
        
        try {
            $callback();
            $this->fail("Expected an expectation failed exception");
        } catch (ExpectationFailedException $e) {
            $this->forgetMockObjects();
        }
    }
    
    public function testCreateCallbackMockSimpleAssertInvoke(): void
    {
        $callback = $this->createCallbackMock($this->once(), ['foo', 'zoo'], 'bar');
        
        $this->assertTrue(is_callable($callback));
        
        $this->assertEquals('bar', $callback('foo', 'zoo'));
    }
    
    public function testCreateCallbackMockSimpleAssertInvokeFail(): void
    {
        $callback = $this->createCallbackMock($this->once(), ['foo'], 'bar');
        
        $this->assertTrue(is_callable($callback));
        
        try {
            $callback('qux');
            $this->fail("Expected an expectation failed exception");
        } catch (ExpectationFailedException $e) {
            $this->forgetMockObjects();
        }
    }
    
    public function testCreateCallbackMockAssertInvoke(): void
    {
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
            $invoke->with('foo', 'zoo')->willReturn('bar');
        });
        
        $this->assertTrue(is_callable($callback));
        
        $this->assertEquals('bar', $callback('foo', 'zoo'));
    }
    
    public function testCreateCallbackMockAssertInvokeFail(): void
    {
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
            $invoke->with('foo');
        });
        
        $this->assertTrue(is_callable($callback));
        
        try {
            $callback('qux');
            $this->fail("Expected an expectation failed exception");
        } catch (ExpectationFailedException $e) {
            $this->forgetMockObjects();
        }
    }
    
    public function testCreateCallbackMockInvalidAssert(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createCallbackMock($this->once(), 'foo');
    }
}
