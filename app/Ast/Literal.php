<?php

namespace App\Ast;

use App\Ast\Expr;
use App\Lexer\Token;

abstract class Literal extends Expr
{
    public function __construct(public Token $token) {}
}
