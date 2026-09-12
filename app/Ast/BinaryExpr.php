<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

class BinaryExpr implements Expr
{
    public function __construct(
        public Expr $left,
        public Token $op,
        public Expr $right
    ) {}
}
