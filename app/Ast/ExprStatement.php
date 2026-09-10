<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Interfaces\Stmt;

class ExprStatement implements Stmt
{
    public function __construct(public Expr $expr) {}
}
