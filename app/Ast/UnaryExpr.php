<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

class UnaryExpr implements Expr
{
    public function __construct(
        public Token $op,
        public Expr $right
    ) {}
}
