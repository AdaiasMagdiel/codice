<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

class CallExpr implements Expr
{
    public function __construct(
        public Token $callee,
        public array $args
    ) {}
}
