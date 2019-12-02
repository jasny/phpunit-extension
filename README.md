Jasny PHPUnit extension
===

[![Build Status](https://travis-ci.org/jasny/phpunit-extension.svg?branch=master)](https://travis-ci.org/jasny/phpunit-extension)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/phpunit-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/phpunit-extension/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/phpunit-extension/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/phpunit-extension/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/phpunit-extension.svg)](https://packagist.org/packages/jasny/phpunit-extension)
[![Packagist License](https://img.shields.io/packagist/l/jasny/phpunit-extension.svg)](https://packagist.org/packages/jasny/phpunit-extension)

Additional functionality for PHPUnit.

* [Callback mock](#callback-mock) - assert that callback is called with correct arguments.
* [Expected warning](#expected-warning) - assert notice/warning is triggered and continue running!
* [Private access](#private-access) - Access private/protected methods and properties.

Installation
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

### Private access

    mixed callPrivateMethod(object $object, string $method, array $args = [])
    mixed setPrivateProperty($object, string $property, $value)
    mixed getPrivateProperty($object, string $property)

Call private and protected methods and get or set private and protected properties.

_You should only test via public methods and properties. When you're required to access private methods or properties
to perform tests, something is likely wrong in the architecture of your code._

```php
use Jasny\PHPUnit\PrivateAccessTrait;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use PrivateAccessTrait;
    
    public function test()
    {
        $object = new MyObject();
    
        $result = $this->callPrivateMethod($object, 'privateMethod', ['foo', 'bar']);
        $this->assertEquals($result, 'foo-bar');

        $value = $this->getPrivateProperty($object, 'privateProperty');
        $this->assertEquals($value, 999);

        $this->setPrivateProperty($object, 'privateProperty', 42);
    }
}
```
