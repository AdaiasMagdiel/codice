<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

class NullLiteral implements Expr
{
    public function __construct(public ?Token $token = null) {}
}
