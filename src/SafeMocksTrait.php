<?php

declare(strict_types=1);

namespace Jasny\PHPUnit;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Overwrite `createMock` method to disable auto-return value generation.
 */
trait SafeMocksTrait
{
    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param string $className
     * @return MockBuilder
     */
    abstract public function getMockBuilder(string $className): MockBuilder;

    /**
     * Returns a mock object for the specified class.
     *
     * @param string|string[] $originalClassName
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType>|string[] $originalClassName
     * @psalm-return MockObject&RealInstanceType
     */
    protected function createMock($originalClassName): MockObject
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->disableAutoReturnValueGeneration()
            ->getMock();
    }
}
