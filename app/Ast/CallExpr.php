<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Loc;

class CallExpr implements Expr
{
    public function __construct(
        public string $callee,
        public array $args,
        public Loc $loc
    ) {}
}
