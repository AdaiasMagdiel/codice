<?php

namespace App\Types;

use Override;

class Booleano implements Type
{
    public function __construct(public bool $value) {}

    #[Override]
    public function __toString()
    {
        return $this->value ? 'vero' : 'falso';
    }
}
