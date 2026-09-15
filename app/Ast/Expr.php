<?php

namespace App\Ast;

use App\Visitors\Visitor;

abstract class Expr
{
    /** @codeCoverageIgnore */
    abstract public function accept(Visitor $visitor);
}
