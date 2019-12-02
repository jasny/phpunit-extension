<?php

declare(strict_types=1);

namespace Jasny\PHPUnit;

/**
 * Trait for accessing private/protected methods and properties.
 */
trait PrivateAccessTrait
{
    /**
     * Call a private or protected method.
     *
     * @param object $object
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    protected function callPrivateMethod(object $object, string $method, array $args = [])
    {
        $refl = new \ReflectionMethod(get_class($object), $method);
        $refl->setAccessible(true);
        
        return $refl->invokeArgs($object, $args);
    }
    
    /**
     * Set a private or protected property.
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     */
    protected function setPrivateProperty(object $object, string $property, $value): void
    {
        $refl = new \ReflectionProperty(get_class($object), $property);
        $refl->setAccessible(true);
        
        $refl->setValue($object, $value);
    }

    /**
     * Get the value of a private or protected property.
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    protected function getPrivateProperty($object, string $property)
    {
        $refl = new \ReflectionProperty(get_class($object), $property);
        $refl->setAccessible(true);

        return $refl->getValue($object);
    }
}
