<?php

namespace App\Ast;

use App\Interfaces\Expr;
use App\Lexer\Token;

abstract class Literal implements Expr
{
    public function __construct(public Token $token) {}
}
