<?php

declare(strict_types=1);

namespace Jasny\PHPUnit\Tests;

use Jasny\PHPUnit\PrivateAccessTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\PHPUnit\PrivateAccessTrait
 */
class PrivateAccessTraitTest extends TestCase
{
    use PrivateAccessTrait;

    protected $object;
    
    public function setUp(): void
    {
        $this->object = new class {
            private $privateProp;
            protected $protectedProp;
            public $publicProp;
            
            private function privateMethod($whois = 'I am') { return "$whois private"; }
            protected function protectedMethod($whois = 'I am') { return "$whois protected"; }
            public function publicMethod($whois = 'I am') { return "$whois public"; }
        };
    }

    public function accessProvider()
    {
        return [
            ['private'],
            ['protected'],
            ['public']
        ];
    }
    
    
    /**
     * @dataProvider accessProvider
     * @param string $access
     */
    public function testCallPrivateMethod($access)
    {
        $result = $this->callPrivateMethod($this->object, $access . 'Method');
        
        $this->assertEquals("I am $access", $result);
    }
    
    /**
     * @dataProvider accessProvider
     * @param string $access
     */
    public function testCallPrivateMethodWithArgument($access)
    {
        $result = $this->callPrivateMethod($this->object, $access . 'Method', ['You are']);
        
        $this->assertEquals("You are $access", $result);
    }
    
    
    /**
     * @dataProvider accessProvider
     * @param string $access
     */
    public function testPrivateProperty($access)
    {
        $this->setPrivateProperty($this->object, $access . 'Prop', 'foo');
        $this->assertEquals('foo', $this->getPrivateProperty($this->object, $access . 'Prop'));
    }
}
