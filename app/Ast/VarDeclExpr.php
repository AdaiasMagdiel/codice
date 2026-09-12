<?php

namespace App\Ast;

use App\Interfaces\Expr;

class VarDeclExpr implements Expr
{
    public function __construct(
        public Identifier $identifier,
        public Expr $value
    ) {}
}
