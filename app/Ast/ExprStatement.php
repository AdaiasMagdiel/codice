<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Ast\Stmt;
use App\Visitors\Visitor;
use Override;

class ExprStatement extends Stmt
{
    public function __construct(public Expr $expr) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitExprStatement($this);
    }
}
