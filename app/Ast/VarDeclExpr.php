<?php

namespace App\Ast;

use App\Visitors\Visitor;
use Override;

class VarDeclExpr extends DeclExpr
{
    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitVarDeclExpr($this);
    }
}
