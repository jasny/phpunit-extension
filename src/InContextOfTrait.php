<?php

namespace Jasny\PHPUnit;

trait InContextOfTrait
{
    public function inContextOf(object $object, \Closure $function) {
        return $function->call($object);
    }
}
