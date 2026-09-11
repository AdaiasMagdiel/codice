<?php

namespace App\Types;

use Override;

class Decimale implements Type
{
    public function __construct(public float $value) {}

    #[Override]
    public function __toString()
    {
        return (string) $this->value;
    }
}
