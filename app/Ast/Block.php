<?php

namespace App\Ast;

use App\Ast\Stmt;
use App\Visitors\Visitor;
use Override;

class Block extends Stmt
{
    public function __construct(public array $statements = []) {}

    #[Override]
    public function accept(Visitor $visitor)
    {
        return $visitor->visitBlock($this);
    }
}
