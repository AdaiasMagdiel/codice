<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Visitors\Visitor;
use App\Lexer\Token;
use Override;

class AssignExpr extends Expr
{
    public function __construct(
        public Token $identifier,
        public Expr $value
    ) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitAssignExpr($this);
    }
}
