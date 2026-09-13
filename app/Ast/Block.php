<?php

namespace App\Ast;

use App\Interfaces\Stmt;

class Block implements Stmt
{
    public function __construct(public array $statements = []) {}
}
