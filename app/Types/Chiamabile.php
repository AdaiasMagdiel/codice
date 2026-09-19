<?php

namespace App\Types;

use Closure;
use Override;

class Chiamabile extends Type
{
    public function __construct(
        public string $name,
        public Closure $fn,
        public Type $returnType = new Nullo()
    ) {}

    #[Override]
    public function __toString()
    {
        return "<chiamabile '{$this->name}'>";
    }
}
