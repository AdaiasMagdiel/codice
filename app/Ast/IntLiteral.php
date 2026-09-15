<?php

namespace App\Ast;

use App\Visitors\Visitor;
use Override;

class IntLiteral extends Literal
{
    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitIntLiteral($this);
    }
}
