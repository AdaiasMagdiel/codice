<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Lexer\Token;

abstract class DeclExpr extends Expr
{
    public function __construct(
        public Token $identifier,
        public Expr $value
    ) {}
}
