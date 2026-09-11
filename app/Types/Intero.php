<?php

namespace App\Types;

use Override;

class Intero implements Type
{
    public function __construct(public int $value) {}

    #[Override]
    public function __toString()
    {
        return (string) $this->value;
    }
}
