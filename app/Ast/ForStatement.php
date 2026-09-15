<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Interfaces\Stmt;

class ForStatement implements Stmt
{
    public function __construct(
        public ?Expr $setup,
        public ?Expr $test,
        public ?Expr $update,
        public Block $body
    ) {}
}
