<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Visitors\Visitor;
use App\Lexer\Token;
use Override;

class PostfixExpr extends Expr
{
    public function __construct(
        public Expr $lvalue,
        public Token $operator
    ) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitPostfixExpr($this);
    }
}
