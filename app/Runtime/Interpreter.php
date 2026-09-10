<?php

namespace App\Runtime;

use App\Ast\CallExpr;
use App\Ast\ExprStatement;
use App\Ast\Identifier;
use App\Ast\Program;
use App\Ast\StringLiteral;
use App\Interfaces\Expr;
use App\Interfaces\Stmt;
use Exception;

class Interpreter
{
    private Environment $environment;

    public function run(Program $program, Environment $environment)
    {
        $this->environment = $environment;

        foreach ($program->statements as $statement) {
            $this->runStatement($statement);
        }
    }

    private function runStatement(Stmt $statement)
    {
        if ($statement instanceof ExprStatement) {
            return $this->runExprStatement($statement->expr);
        }

        $class = get_class($statement);
        throw new Exception("Istruzione inattesa '{$class}'.");
    }

    private function runExprStatement(Expr $expr)
    {
        return $this->runExpression($expr);
    }

    private function runExpression(Expr $expr)
    {
        if ($expr instanceof StringLiteral) {
            return $expr->token->lexeme;
        } else if ($expr instanceof Identifier) {
            return $this->environment->getIdentifier($expr);
        } else if ($expr instanceof CallExpr) {
            $fn = $this->environment->getFunction($expr->callee, $expr->loc);
            return $fn(...array_map($this->runExpression(...), $expr->args));
        }

        $class = get_class($expr);
        throw new Exception("Espressione inattesa '{$class}'.");
    }
}
