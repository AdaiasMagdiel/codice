<?php

namespace App\Ast;

use App\Visitors\Visitor;

abstract class Stmt
{
    abstract public function accept(Visitor $visitor);
}
