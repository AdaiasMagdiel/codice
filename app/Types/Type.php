<?php

namespace App\Types;

abstract class Type
{
    abstract public function __toString(): string;
    public static function is(mixed $value): bool
    {
        return $value instanceof static;
    }
}
