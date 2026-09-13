<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

abstract class DeclExpr implements Expr
{
    public function __construct(
        public Token $identifier,
        public Expr $value
    ) {}
}
