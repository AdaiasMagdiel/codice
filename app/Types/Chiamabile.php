<?php

namespace App\Types;

use Closure;
use Override;

class Chiamabile implements Type
{
    public function __construct(
        public string $name,
        public Closure $fn
    ) {}

    #[Override]
    public function __toString()
    {
        return "<chiamabile '{$this->name}'>";
    }
}
