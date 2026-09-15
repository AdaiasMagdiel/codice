<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Visitors\Visitor;
use App\Lexer\Token;
use Override;

class Identifier extends Expr
{
    public function __construct(public Token $token) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitIdentifier($this);
    }
}
