<?php

namespace App\Types;

use Override;

class Decimale implements Type
{
    public function __construct(public float $value) {}

    #[Override]
    public function __toString()
    {
        $value = (string) $this->value;

        return str_contains($value, '.') ? $value : $value . '.0';
    }
}
