<?php

namespace App\Ast;

use App\Visitors\Visitor;
use Override;

class ConstDeclExpr extends DeclExpr
{
    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitConstDeclExpr($this);
    }
}
