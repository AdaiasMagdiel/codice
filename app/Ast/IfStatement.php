<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Ast\Stmt;
use App\Visitors\Visitor;
use Override;

class IfStatement extends Stmt
{
    public function __construct(
        public Expr $condition,
        public Stmt $then,
        public ?Stmt $else = null
    ) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitIfStatement($this);
    }
}
