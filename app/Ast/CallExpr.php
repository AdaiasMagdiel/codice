<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Visitors\Visitor;
use App\Lexer\Token;
use Override;

class CallExpr extends Expr
{
    public function __construct(
        public Token $callee,
        public array $args
    ) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitCallExpr($this);
    }
}
