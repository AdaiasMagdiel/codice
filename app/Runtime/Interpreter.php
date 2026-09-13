<?php

namespace App\Runtime;

use App\Ast\AssignExpr;
use App\Ast\BinaryExpr;
use App\Ast\Block;
use App\Ast\BoolLiteral;
use App\Ast\CallExpr;
use App\Ast\ConstDeclExpr;
use App\Ast\DeclExpr;
use App\Ast\ExprStatement;
use App\Ast\FloatLiteral;
use App\Ast\Identifier;
use App\Ast\IfStatement;
use App\Ast\IntLiteral;
use App\Ast\NullLiteral;
use App\Ast\Program;
use App\Ast\StringLiteral;
use App\Ast\UnaryExpr;
use App\Ast\VarDeclExpr;
use App\Enums\TokenType;
use App\Exceptions\DivisionByZeroError;
use App\Exceptions\RuntimeError;
use App\Exceptions\TypeError;
use App\Interfaces\Expr;
use App\Interfaces\Stmt;
use App\Lexer\Token;
use App\Types\Booleano;
use App\Types\Chiamabile;
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

    private function expectType(
        Token $token,
        array|Type $values,
        array|string $expecteds
    ): void {
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

    private function toBool(Expr $expr): Booleano
    {
        $value = $this->runExpression($expr);

        if ($value instanceof Booleano) {
            return $value;
        }
        if ($value instanceof Intero) {
            return new Booleano($value->value !== 0);
        }
        if ($value instanceof Decimale) {
            return new Booleano($value->value !== 0.0);
        }
        if ($value instanceof Stringa) {
            return new Booleano($value->value !== "");
        }
        if ($value instanceof Nullo) {
            return new Booleano(false);
        }
        if ($value instanceof Chiamabile) {
            $token = match (true) {
                $expr instanceof Identifier => $expr->token,
                $expr instanceof CallExpr => $expr->callee,
                $expr instanceof AssignExpr, $expr instanceof DeclExpr => $expr->identifier,
            };

            throw new TypeError(
                "'{$value->name}' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?",
                $token->loc
            );
        }

        $class = get_class($value);
        throw new Exception("Impossibile convertire '{$class}' in booleano.\n");
    }

    private function toCodiceType(mixed $value): Type
    {
        if ($value instanceof Type) return $value;

        if (is_null($value)) return new Nullo();
        if (is_int($value)) return new Intero($value);
        if (is_string($value)) return new Stringa($value);
        if (is_float($value)) return new Decimale($value);
        if (is_bool($value)) return new Booleano($value);

        $type = get_debug_type($value);
        throw new Exception(
            "Impossibile convertire il valore nativo di tipo '{$type}' in un Type di Codice.\n"
        );
    }

    public function run(Program $program, Environment $environment): Nullo
    {
        $this->environment = $environment;

        foreach ($program->statements as $statement) {
            $this->runStatement($statement);
        }

        return new Nullo;
    }

    private function runStatement(Stmt $statement): Nullo
    {
        if ($statement instanceof IfStatement) {
            return $this->runIfStatement($statement);
        }

        if ($statement instanceof Block) {
            return $this->runBlock($statement);
        }

        if ($statement instanceof ExprStatement) {
            return $this->runExprStatement($statement);
        }

        $class = get_class($statement);
        throw new Exception("Istruzione inattesa '{$class}'.\n");
    }

    private function runIfStatement(IfStatement $stmt): Nullo
    {
        if ($this->toBool($stmt->condition)->value) {
            $this->runStatement($stmt->then);
        } else if (!is_null($stmt->else)) {
            $this->runStatement($stmt->else);
        }

        return new Nullo();
    }

    private function runBlock(Block $stmt): Nullo
    {
        $enclosingEnv = $this->environment;
        $this->environment = new Environment($enclosingEnv);

        foreach ($stmt->statements as $statement) {
            $this->runStatement($statement);
        }

        $this->environment = $enclosingEnv;
        return new Nullo();
    }

    private function runExprStatement(ExprStatement $stmt): Nullo
    {
        $this->runExpression($stmt->expr);
        return new Nullo();
    }

    private function runExpression(Expr $expr): Type
    {
        // functions
        if ($expr instanceof CallExpr) {
            $fn = $this->environment->get($expr->callee);

            if (!($fn instanceof Chiamabile)) {
                throw new RuntimeError(
                    "Atteso che '{$expr->callee->lexeme}' fosse una funzione.",
                    $expr->callee->loc
                );
            }

            $return = ($fn->fn)(...array_map($this->runExpression(...), $expr->args));
            return $this->toCodiceType($return);
        }

        // variable declaration
        else if ($expr instanceof VarDeclExpr) {
            $value = $this->runExpression($expr->value);
            $this->environment->define($expr->identifier, $value);

            return $value;
        }

        // constant declaration
        else if ($expr instanceof ConstDeclExpr) {
            $value = $this->runExpression($expr->value);
            $this->environment->defineConst($expr->identifier, $value);

            return $value;
        }

        // assignments
        else if ($expr instanceof AssignExpr) {
            $value = $this->runExpression($expr->value);
            $this->environment->assign($expr->identifier, $value);

            return $value;
        }

        // strings
        else if ($expr instanceof StringLiteral) {
            return new Stringa($expr->token->lexeme);
        }

        // identificators
        else if ($expr instanceof Identifier) {
            return $this->environment->get($expr->token);
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
                    return $this->toCodiceType($right->value * -1);

                default:
                    throw new Exception("Operatore unario '{$op->lexeme}' non implementato.\n");
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

                    if ($left instanceof Stringa || $right instanceof Stringa) {
                        return new Stringa((string) $left->value . (string) $right->value);
                    }

                    return $this->toCodiceType($left->value + $right->value);

                case TokenType::MINUS:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class]);
                    return $this->toCodiceType($left->value - $right->value);

                case TokenType::STAR:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class, Stringa::class]);

                    // string x string
                    if ($left instanceof Stringa && $right instanceof Stringa) {
                        throw new TypeError("Operatore '*' non applicabile a due stringhe.", $op->loc);
                    }

                    // string x float | float x string - invalid
                    if (($left instanceof Stringa && $right instanceof Decimale) || ($left instanceof Decimale && $right instanceof Stringa)) {
                        throw new TypeError(
                            "Operatore '*' non applicabile tra {$this->typeName($left)} e {$this->typeName($right)}.",
                            $op->loc
                        );
                    }

                    // string x int | int x string
                    if ($left instanceof Stringa || $right instanceof Stringa) {
                        [$str, $times] = $left instanceof Stringa
                            ? [$left->value, $right->value]
                            : [$right->value, $left->value];

                        if ($times < 0) {
                            throw new TypeError("Il moltiplicatore della stringa non può essere negativo.", $op->loc);
                        }

                        return new Stringa(str_repeat($str, $times));
                    }

                    return $this->toCodiceType($left->value * $right->value);

                case TokenType::SLASH:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class]);

                    if ($right->value == 0) {
                        throw new DivisionByZeroError("Impossibile dividere per zero.", $op->loc);
                    }

                    return new Decimale((float) $left->value / (float) $right->value);

                default:
                    throw new Exception("Operatore binario '{$op->lexeme}' non implementato.\n");
            }
        }

        $class = get_class($expr);
        throw new Exception("Espressione inattesa '{$class}'.\n");
    }
}
