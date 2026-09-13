<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Interfaces\Stmt;

class IfStatement implements Stmt
{
    public function __construct(
        public Expr $condition,
        public Stmt $then,
        public ?Stmt $else = null
    ) {}
}
