Jasny PHPUnit extension
===

[![Build status](https://github.com/jasny/phpunit-extension/actions/workflows/php.yml/badge.svg)](https://github.com/jasny/phpunit-extension/actions/workflows/php.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/phpunit-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/phpunit-extension/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/phpunit-extension/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/phpunit-extension/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/phpunit-extension.svg)](https://packagist.org/packages/jasny/phpunit-extension)
[![Packagist License](https://img.shields.io/packagist/l/jasny/phpunit-extension.svg)](https://packagist.org/packages/jasny/phpunit-extension)

Additional functionality for PHPUnit.

* [Callback mock](#callback-mock) - assert that callback is called with correct arguments.
* [Expected warning](#expected-warning) - assert notice/warning is triggered and continue running.
* [In context of](#in-context-of) - Access private/protected methods and properties.
* [Consecutive calls](#consecutive-calls) - Assert multiple calls with different arguments.

* Installation
---

    composer require jasny/phpunit-extension

Usage
---

### Callback mock

    MockObject createCallbackMock(InvocationOrder $matcher, array|Closure $assert = null, $return = null)

The method takes either the expected arguments as array and the return value or a `Closure`. If a `Closure` is given,
it will be called with an `InvocationMocker`, which can be used for more advanced matching.

```php
use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use CallbackMockTrait;
    
    public function testSingleCall()
    {
        $callback = $this->createCallbackMock($this->once(), ['abc'], 10);
        function_that_invokes($callback);
    }

    public function testConsecutiveCalls()
    {
        $callback = $this->createCallbackMock(
            $this->exactly(2),
            function(InvocationMocker $invoke) {
                $invoke
                    ->withConsecutive(['abc'], ['xyz'])
                    ->willReturnOnConsecutiveCalls(10, 42);
            }
        );

        function_that_invokes($callback);
    }
}
```

### Safe Mocks

The `SafeMocksTrait` overwrites the `createMock` method to disable auto-return value generation. This means that the
test will fail if any method is called that isn't configured. 

```php
use Jasny\PHPUnit\SafeMocksTrait;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use SafeMocksTrait;
    
    public function test()
    {
        $foo = $this->createMock(Foo::class);
        $foo->expects($this->once())->method('hello');
    
        $foo->hello();
        $foo->bye();
    }
}
```

In the example above, the method `bye()` isn't configured for `$foo`. Normally the test would succeed, but with
`SafeMocksTrait` this test will result in the failure

    Return value inference disabled and no expectation set up for Foo::bye()

### Expected warning

`ExpectedWarningTrait` overwrites the following methods;

* `expectNotice()`
* `expectNoticeMessage(string $message)`
* `expectNoticeMessageMatches(string $regexp)` 
* `expectWarning()`
* `expectWarningMessage(string $message)`
* `expectWarningMessageMatches(string $regexp)` 
* `expectDeprecation()`
* `expectDeprecationMessage(string $message)`
* `expectDeprecationMessageMatches(string $regexp)`

Take the following example function;

```php
function my_old_func(float $a)
{
    trigger_error("Use my_new_func() instead", E_USER_DEPRECATED);
    return (int)($a * 100);
}
```

PHPUnit converts notices and warning to exceptions. While you can catch these exceptions through methods like
`expectDeprecation`, any code after the the notice/warning isn't run and therefor untestable.

The following test succeeds, while it should fail.

```php
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function test()
    {
        $this->expectDeprecation();
    
        $result = my_old_func(0.42); // <-- Will exit on deprecation

        $this->assertEquals(89, $result); // This should fail, but is never executed
    }
}
```

`ExpectedWarningTrait` sets a custom error handler, that catches expected warnings and notices, without converting them
to exceptions. Code will continue to run.

After all other assertions succeed, the code will check if there are any expected warnings/notices that haven't been
triggered. 

```php
use Jasny\PHPUnit\ExpectWarningTrait;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use ExpectWarningTrait;

    public function test()
    {
        $this->expectDeprecation();
    
        $result = my_old_func(0.42); // <-- Will NOT exit

        $this->assertEquals(89, $result); // Will fail
    }
}
```

### In context of

    mixed inContextOf(object $object, \Closure $function)

The function is called in context of the given object. This allows to call private and protected methods and get or set
private and protected properties.

```php
use Jasny\PHPUnit\InContextOfTrait;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use InContextOfTrait;
    
    public function testCallPrivateMethod()
    {
        $object = new MyObject();
    
        $result = $this->inContextOf($object, fn($object) => $object->privateMethod('foo', 'bar'));
        $this->assertEquals($result, 'foo-bar');
    }
    
    
    public function testGetPrivateProperty()
    {
        $value = $this->inContextOf($object, fn($object) => $object->privateProperty);
        $this->assertEquals($value, 999);
    }
    
    
    /** Alternatively, do the assertion in the closure */
    public function testAssertPrivateProperty()
    {
        $this->inContextOf($object, fn($object) => $this->assertEquals($object->privateProperty, 999));
    }

    public function testSetPrivateProperty()
    {
        $this->inContextOf($object, fn($object) => $object->privateProperty = 42);
    }
}
```

_**Beware:** You should only test via public methods and properties. When you're required to access private methods or
properties to perform tests, something is likely wrong in the architecture of your code._

### Consecutive calls

`ConsecutiveTrait` is a replacement for PHPUnit's `withConsecutive` method which was removed in PHPUnit 10.

```php
use Jasny\PHPUnit\ConsecutiveTrait;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use ConsecutiveTrait;

    public function test()
    {
        $mock = $this->createMock(MyClass::class);
        $mock->expects($this->exactly(2))
            ->method('foo')
            ->with(...$this->consecutive(
                ['a', 1],
                ['b', 2],
            ))
            ->willReturnOnConsecutiveCalls(10, 42);
        
        $this->assertEquals(10, $mock->foo('a', 1));
        $this->assertEquals(42, $mock->foo('b', 2));
    }
}
```
