<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @covers \Jasny\PHPUnit\CallbackMockTrait
 */
class CallbackMockTraitTest extends TestCase
{
    use CallbackMockTrait;

    protected function forgetMockObjects()
    {
        $refl = new \ReflectionProperty(TestCase::class, 'mockObjects');
        $refl->setAccessible(true);
        
        $refl->setValue($this, []);
    }

    public function testCreateCallbackMock()
    {
        $callback = $this->createCallbackMock($this->any());
        
        $this->assertTrue(is_callable($callback));
        
        $this->assertNull($callback('foo'));
        $this->assertNull($callback('bar'));
        $this->assertNull($callback('zoo'));
    }
    
    public function testCreateCallbackMockNeverInvoke()
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
    
    public function testCreateCallbackMockSimpleAssertInvoke()
    {
        $callback = $this->createCallbackMock($this->once(), ['foo', 'zoo'], 'bar');
        
        $this->assertTrue(is_callable($callback));
        
        $this->assertEquals('bar', $callback('foo', 'zoo'));
    }
    
    public function testCreateCallbackMockSimpleAssertInvokeFail()
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
    
    public function testCreateCallbackMockAssertInvoke()
    {
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
            $invoke->with('foo', 'zoo')->willReturn('bar');
        });
        
        $this->assertTrue(is_callable($callback));
        
        $this->assertEquals('bar', $callback('foo', 'zoo'));
    }
    
    public function testCreateCallbackMockAssertInvokeFail()
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
    
    public function testCreateCallbackMockInvalidAssert()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createCallbackMock($this->once(), 'foo');
    }
}

