Jasny PHPUnit extension
===

[![Build Status](https://travis-ci.org/jasny/phpunit-extension.svg?branch=master)](https://travis-ci.org/jasny/phpunit-extension)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/phpunit-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/phpunit-extension/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/phpunit-extension/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/phpunit-extension/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/phpunit-extension.svg)](https://packagist.org/packages/jasny/phpunit-extension)
[![Packagist License](https://img.shields.io/packagist/l/jasny/phpunit-extension.svg)](https://packagist.org/packages/jasny/phpunit-extension)

Additional functionality for PHPUnit

Installation
---

    composer require jasny/phpunit-extension

Usage
---

The `Jasny\TestHelper` trait adds methods to a PHPUnit test case.

#### callPrivateMethod

    mixed callPrivateMethod(object $object, string $method, array $args = [])

Call a private or protected method.

#### setPrivateProperty

    mixed setPrivateProperty($object, string $property, $value)

Set a private or protected property.

#### assertLastError

    void assertLastError(int $type, string $message = null)

Assert the last error. This can be used to verify an error when ignoring a warning or exception using `@`.

#### createCallbackMock

    MockObject createCallbackMock(Invocation $matcher, $assert = null, $return = null)

Create mock for next callback.

```php
$callback = $this->createCallbackMock($this->once(), ['abc'], 10);
```

```php
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;

$callback = $this->createCallbackMock(
  $this->once(),
  function(InvocationMocker $invoke) {
    $invoke->with('abc')->willReturn(10);
  }
);
```

