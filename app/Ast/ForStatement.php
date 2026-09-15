<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Ast\Stmt;
use App\Visitors\Visitor;
use Override;

class ForStatement extends Stmt
{
    public function __construct(
        public ?Expr $setup,
        public ?Expr $test,
        public ?Expr $update,
        public Block $body
    ) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitForStatement($this);
    }
}
