<?php

namespace App\Ast;

use App\Visitors\Visitor;
use Override;

class BoolLiteral extends Literal
{
    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitBoolLiteral($this);
    }
}
