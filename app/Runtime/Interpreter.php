<?php

namespace App\Runtime;

use App\Ast\BinaryExpr;
use App\Ast\BoolLiteral;
use App\Ast\CallExpr;
use App\Ast\ExprStatement;
use App\Ast\FloatLiteral;
use App\Ast\Identifier;
use App\Ast\IntLiteral;
use App\Ast\NullLiteral;
use App\Ast\Program;
use App\Ast\StringLiteral;
use App\Ast\UnaryExpr;
use App\Enums\TokenType;
use App\Exceptions\DivisionByZeroError;
use App\Exceptions\TypeError;
use App\Interfaces\Expr;
use App\Interfaces\Stmt;
use App\Lexer\Token;
use App\Types\Booleano;
use App\Types\Decimale;
use App\Types\Intero;
use App\Types\Nullo;
use App\Types\Stringa;
use App\Types\Type;
use Exception;
use TypeError as GlobalTypeError;

class Interpreter
{
    private Environment $environment;

    private function typeName(string|Type $type): string
    {
        $class = is_string($type) ? $type : get_class($type);

        return strtolower(substr($class, strrpos($class, '\\') + 1));
    }

    private function expectType(Token $token, array|Type $values, array|string $expecteds): void
    {
        $expecteds = is_array($expecteds) ? $expecteds : [$expecteds];
        $values = is_array($values) ? $values : [$values];

        foreach ($values as $value) {
            foreach ($expecteds as $expected) {
                if ($value instanceof $expected) {
                    continue 2;
                }
            }

            $expectedNames = implode(' o ', array_map($this->typeName(...), $expecteds));
            $foundName = $this->typeName($value);

            throw new TypeError(
                "Atteso {$expectedNames}, ma trovato {$foundName}.",
                $token->loc
            );
        }
    }

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
        // functions
        if ($expr instanceof CallExpr) {
            $fn = $this->environment->getFunction($expr->callee, $expr->loc);
            $return = $fn(...array_map($this->runExpression(...), $expr->args));

            return is_null($return) ? new Nullo() : $return;
        }

        // strings
        else if ($expr instanceof StringLiteral) {
            return new Stringa($expr->token->lexeme);
        }

        // identificators
        else if ($expr instanceof Identifier) {
            return $this->environment->getIdentifier($expr);
        }

        // null
        else if ($expr instanceof NullLiteral) {
            return new Nullo();
        }

        // bool
        else if ($expr instanceof BoolLiteral) {
            return new Booleano($expr->token->lexeme === 'vero');
        }

        // integers
        else if ($expr instanceof IntLiteral) {
            return new Intero($expr->token->lexeme);
        }

        // floats
        else if ($expr instanceof FloatLiteral) {
            return new Decimale($expr->token->lexeme);
        }

        // unary
        else if ($expr instanceof UnaryExpr) {
            $op = $expr->op;
            $right = $this->runExpression($expr->right);

            switch ($op->type) {
                case TokenType::PLUS:
                    $this->expectType($op, $right, [Intero::class, Decimale::class]);
                    return $right;

                case TokenType::MINUS:
                    $this->expectType($op, $right, [Intero::class, Decimale::class]);
                    $right->value *= -1;
                    return $right;

                default:
                    throw new Exception("Operatore unario '{$op->lexeme}' non implementato.");
            }
        }

        // binary
        else if ($expr instanceof BinaryExpr) {
            $left = $this->runExpression($expr->left);
            $op = $expr->op;
            $right = $this->runExpression($expr->right);

            switch ($op->type) {
                case TokenType::PLUS:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class, Stringa::class]);

                    if (is_string($left->value) || is_string($right->value)) {
                        $res = (string) $left->value . (string) $right->value;
                        return new Stringa($res);
                    } else if (is_float($left->value) || is_float($right->value)) {
                        $res = (float) $left->value + (float) $right->value;
                        return new Decimale($res);
                    }

                    $res = $left->value + $right->value;
                    return new Intero($res);

                case TokenType::MINUS:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class]);

                    if (is_float($left->value) || is_float($right->value)) {
                        $res = (float) $left->value - (float) $right->value;
                        return new Decimale($res);
                    }

                    $res = $left->value - $right->value;
                    return new Intero($res);

                case TokenType::STAR:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class, Stringa::class]);

                    // string x string
                    if (is_string($left->value) && is_string($right->value)) {
                        throw new TypeError("Operatore '*' non applicabile a due stringhe.", $op->loc);
                    }

                    // string x float | float x string - invalid
                    if ((is_string($left->value) && is_float($right->value)) || (is_float($left->value) && is_string($right->value))) {
                        throw new TypeError(
                            "Operatore '*' non applicabile tra {$this->typeName($left)} e {$this->typeName($right)}.",
                            $op->loc
                        );
                    }

                    // string x int | int x string
                    if (is_string($left->value) || is_string($right->value)) {
                        $str = is_string($left->value) ? $left->value : $right->value;
                        $times = is_int($left->value) ? $left->value : $right->value;

                        if ($times < 0) {
                            throw new TypeError("Il moltiplicatore della stringa non può essere negativo.", $op->loc);
                        }

                        return new Stringa(str_repeat($str, $times));
                    }

                    // float
                    if (is_float($left->value) || is_float($right->value)) {
                        $res = (float) $left->value * (float) $right->value;
                        return new Decimale($res);
                    }

                    // int
                    $res = $left->value * $right->value;
                    return new Intero($res);

                case TokenType::SLASH:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class]);

                    if ($right->value == 0) {
                        throw new DivisionByZeroError("Impossibile dividere per zero.", $op->loc);
                    }

                    $res = (float) $left->value / (float) $right->value;
                    return new Decimale($res);
            }
        }

        $class = get_class($expr);
        throw new Exception("Espressione inattesa '{$class}'.");
    }
}
