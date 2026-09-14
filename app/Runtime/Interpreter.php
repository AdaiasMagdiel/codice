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
use App\Ast\PostfixExpr;
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

    private function toBool(Type $value, callable $token): Booleano
    {
        /** @var Stringa|Intero|Decimale|Chiamabile|Nullo|Booleano $value */

        if (Booleano::is($value)) {
            return $value;
        }
        if (Intero::is($value)) {
            return new Booleano($value->value !== 0);
        }
        if (Decimale::is($value)) {
            return new Booleano($value->value !== 0.0);
        }
        if (Stringa::is($value)) {
            return new Booleano($value->value !== "");
        }
        if (Nullo::is($value)) {
            return new Booleano(false);
        }
        if (Chiamabile::is($value)) {
            throw new TypeError(
                "'{$value->name}' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?",
                $token()->loc
            );
        }

        $class = get_class($value);
        throw new Exception("Impossibile convertire '{$class}' in booleano.\n");
    }

    private function exprToken(Expr $expr): Token
    {
        return match (true) {
            $expr instanceof Identifier => $expr->token,
            $expr instanceof CallExpr => $expr->callee,
            $expr instanceof AssignExpr, $expr instanceof DeclExpr => $expr->identifier,
            default => throw new Exception("Impossibile determinare il token dell'espressione '" . get_class($expr) . "'.\n"),
        };
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
        $condition = $this->runExpression($stmt->condition);

        if ($this->toBool($condition, fn() => $this->exprToken($stmt->condition))->value) {
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

            if (!Chiamabile::is($fn)) {
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

        // postfixes
        else if ($expr instanceof PostfixExpr) {
            $op = $expr->operator;
            /** @var Intero|Decimale $currentValue */
            $currentValue = $this->runExpression($expr->lvalue);

            if (!$expr->lvalue instanceof Identifier) {
                $operation = $op->type === TokenType::INCREMENT
                    ? 'incremento'
                    : 'decremento';
                throw new RuntimeError("Richiesto un lvalue come operando di {$operation}.", $expr->lvalue->token->loc);
            }

            $this->expectType($op, $currentValue, [Intero::class, Decimale::class]);
            $newValue = $this->toCodiceType(
                $op->type === TokenType::INCREMENT
                    ? $currentValue->value + 1
                    : $currentValue->value - 1
            );
            $this->environment->assign($expr->lvalue->token, $newValue);

            return $currentValue;
        }

        // unary
        else if ($expr instanceof UnaryExpr) {
            $op = $expr->op;
            /** @var Intero|Decimale|Stringa $right */
            $right = $this->runExpression($expr->right);

            switch ($op->type) {
                case TokenType::PLUS:
                    $this->expectType($op, $right, [Intero::class, Decimale::class]);
                    return $right;

                case TokenType::MINUS:
                    $this->expectType($op, $right, [Intero::class, Decimale::class]);
                    return $this->toCodiceType($right->value * -1);

                case TokenType::INCREMENT:
                case TokenType::DECREMENT:
                    if (!$expr->right instanceof Identifier) {
                        $operation = $op->type === TokenType::INCREMENT
                            ? 'incremento'
                            : 'decremento';
                        throw new RuntimeError("Richiesto un lvalue come operando di {$operation}.", $expr->right->token->loc);
                    }

                    $this->expectType($op, $right, [Intero::class, Decimale::class]);
                    $value = $this->toCodiceType(
                        $op->type === TokenType::INCREMENT
                            ? $right->value + 1
                            : $right->value - 1
                    );
                    $this->environment->assign($expr->right->token, $value);

                    return $value;

                default:
                    throw new Exception("Operatore unario '{$op->lexeme}' non implementato.\n");
            }
        }

        // binary
        else if ($expr instanceof BinaryExpr) {
            $op = $expr->op;

            if ($op->type === TokenType::AND) {
                $leftBool = $this->toBool(
                    $this->runExpression($expr->left),
                    fn() => $this->exprToken($expr->left)
                );

                if ($leftBool->value) {
                    return $this->toBool(
                        $this->runExpression($expr->right),
                        fn() => $this->exprToken($expr->right)
                    );
                }

                return $leftBool;
            }

            if ($op->type === TokenType::OR) {
                $leftBool = $this->toBool(
                    $this->runExpression($expr->left),
                    fn() => $this->exprToken($expr->left)
                );

                if ($leftBool->value) {
                    return $leftBool;
                }

                return $this->toBool(
                    $this->runExpression($expr->right),
                    fn() => $this->exprToken($expr->right)
                );
            }

            $left = $this->runExpression($expr->left);
            $right = $this->runExpression($expr->right);

            switch ($op->type) {
                case TokenType::PLUS:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class, Stringa::class]);

                    /** @var Intero|Decimale|Stringa $left */
                    /** @var Intero|Decimale|Stringa $right */

                    if (Stringa::is($left) || Stringa::is($right)) {
                        return new Stringa((string) $left->value . (string) $right->value);
                    }

                    return $this->toCodiceType($left->value + $right->value);

                case TokenType::MINUS:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class]);

                    /** @var Intero|Decimale $left */
                    /** @var Intero|Decimale $right */

                    return $this->toCodiceType($left->value - $right->value);

                case TokenType::STAR:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class, Stringa::class]);

                    /** @var Intero|Decimale|Stringa $left */
                    /** @var Intero|Decimale|Stringa $right */

                    // string x string
                    if (Stringa::is($left) && Stringa::is($right)) {
                        throw new TypeError("Operatore '*' non applicabile a due stringhe.", $op->loc);
                    }

                    // string x float | float x string - invalid
                    if ((Stringa::is($left) && Decimale::is($right)) || (Decimale::is($left) && Stringa::is($right))) {
                        throw new TypeError(
                            "Operatore '*' non applicabile tra {$this->typeName($left)} e {$this->typeName($right)}.",
                            $op->loc
                        );
                    }

                    // string x int | int x string
                    if (Stringa::is($left) || Stringa::is($right)) {
                        [$str, $times] = Stringa::is($left)
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

                    /** @var Intero|Decimale $left */
                    /** @var Intero|Decimale $right */

                    if ($right->value == 0) {
                        throw new DivisionByZeroError("Impossibile dividere per zero.", $op->loc);
                    }

                    return new Decimale((float) $left->value / (float) $right->value);

                case TokenType::LESS:
                case TokenType::GREATER:
                case TokenType::GREATER_EQUAL:
                case TokenType::LESS_EQUAL:
                    $this->expectType($op, [$left, $right], [Intero::class, Decimale::class, Stringa::class]);

                    if (Stringa::is($left) xor Stringa::is($right)) {
                        throw new TypeError(
                            "Operatore '{$op->lexeme}' non applicabile tra {$this->typeName($left)} e {$this->typeName($right)}.",
                            $op->loc
                        );
                    }

                    /** @var Intero|Decimale|Stringa $left */
                    /** @var Intero|Decimale|Stringa $right */
                    return $this->toCodiceType(match ($op->type) {
                        TokenType::LESS => $left->value < $right->value,
                        TokenType::GREATER => $left->value > $right->value,
                        TokenType::LESS_EQUAL => $left->value <= $right->value,
                        TokenType::GREATER_EQUAL => $left->value >= $right->value,
                    });

                case TokenType::EQUAL:
                case TokenType::NOT_EQUAL:
                    /** @var Intero|Decimale|Stringa|Chiamabile|Nullo|Booleano $left */
                    /** @var Intero|Decimale|Stringa|Chiamabile|Nullo|Booleano $right */

                    $equal = match (true) {
                        Nullo::is($left) && Nullo::is($right) => true,
                        Chiamabile::is($left) || Chiamabile::is($right) => $left === $right,
                        get_class($left) !== get_class($right) => false,
                        default => $left->value === $right->value,
                    };

                    return $this->toCodiceType($op->type === TokenType::EQUAL ? $equal : !$equal);

                default:
                    throw new Exception("Operatore binario '{$op->lexeme}' non implementato.\n");
            }
        }

        $class = get_class($expr);
        throw new Exception("Espressione inattesa '{$class}'.\n");
    }
}
