<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Visitors\Visitor;
use App\Lexer\Token;
use Override;

class UnaryExpr extends Expr
{
    public function __construct(
        public Token $op,
        public Expr $right
    ) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitUnaryExpr($this);
    }
}
