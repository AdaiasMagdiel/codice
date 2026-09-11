<?php

namespace App\Types;

use Override;

class Stringa implements Type
{
    public function __construct(public string $value) {}

    #[Override]
    public function __toString()
    {
        return $this->value;
    }
}
