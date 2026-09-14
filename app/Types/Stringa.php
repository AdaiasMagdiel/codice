<?php

namespace App\Types;

use Override;

class Stringa extends Type
{
    public function __construct(public string $value) {}

    #[Override]
    public function __toString()
    {
        return $this->value;
    }
}
