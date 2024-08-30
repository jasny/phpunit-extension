<?php

namespace Jasny\PHPUnit;

trait InContextOfTrait
{
    public function inContextOf(object $object, \Closure $function)
    {
        return $function->bindTo($this, $object)($object);
    }
}
