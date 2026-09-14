<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

class PostfixExpr implements Expr
{
    public function __construct(
        public Expr $expr,
        public Token $operator
    ) {}
}
