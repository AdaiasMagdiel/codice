<?php

namespace App\Ast;

use App\Visitors\Visitor;
use Override;

class NullLiteral extends Literal
{
    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitNullLiteral($this);
    }
}
