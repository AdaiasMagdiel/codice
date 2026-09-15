<?php

namespace App\Ast;

use App\Visitors\Visitor;
use Override;

class StringLiteral extends Literal
{
    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitStringLiteral($this);
    }
}
