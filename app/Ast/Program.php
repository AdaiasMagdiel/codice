<?php

namespace App\Ast;

use App\Visitors\Visitor;

class Program
{
    public function __construct(public array $statements = []) {}

    public function accept(Visitor $visitor)
    {
        return $visitor->visitProgram($this);
    }
}
