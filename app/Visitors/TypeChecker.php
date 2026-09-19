<?php

namespace App\Visitors;

use App\Ast;
use App\Enums\TokenType;
use App\Exceptions\DivisionByZeroError;
use App\Exceptions\RuntimeError;
use App\Exceptions\TypeError;
use App\Lexer\Token;
use App\Runtime\Environment;
use App\Types;
use Exception;
use Override;

class TypeChecker implements Visitor
{
    public function __construct(private Environment $environment) {}

    private function exprToken(Ast\Expr $expr): Token
    {
        return match (true) {
            $expr instanceof Ast\Identifier => $expr->token,
            $expr instanceof Ast\CallExpr => $expr->callee,
            $expr instanceof Ast\AssignExpr, $expr instanceof Ast\DeclExpr => $expr->identifier,
            default => throw new Exception("Impossibile determinare il token dell'espressione '" . get_class($expr) . "'.\n"),
        };
    }

    private function toBool(Types\Type $value, callable $token): Types\Booleano
    {
        if (Types\Booleano::is($value)) {
            return $value;
        }
        if (Types\Intero::is($value)) {
            return new Types\Booleano(true);
        }
        if (Types\Decimale::is($value)) {
            return new Types\Booleano(true);
        }
        if (Types\Stringa::is($value)) {
            return new Types\Booleano(true);
        }
        if (Types\Nullo::is($value)) {
            return new Types\Booleano(false);
        }
        if (Types\Chiamabile::is($value)) {
            /** @var Types\Chiamabile $value */
            throw new TypeError(
                "'{$value->name}' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?",
                $token()->loc
            );
        }

        $class = get_class($value);
        throw new Exception("Impossibile convertire '{$class}' in booleano.\n");
    }

    private function toCodiceType(mixed $value): Types\Type
    {
        if ($value instanceof Types\Type) return $value;

        if (is_null($value)) return new Types\Nullo();
        if (is_int($value)) return new Types\Intero($value);
        if (is_string($value)) return new Types\Stringa($value);
        if (is_float($value)) return new Types\Decimale($value);
        if (is_bool($value)) return new Types\Booleano($value);

        $type = get_debug_type($value);
        throw new Exception(
            "Impossibile convertire il valore nativo di tipo '{$type}' in un Type di Codice.\n"
        );
    }

    private function typeName(string|Types\Type $type): string
    {
        $class = is_string($type) ? $type : get_class($type);
        return strtolower(substr($class, strrpos($class, '\\') + 1));
    }

    private function expectType(
        Token $token,
        array|Types\Type $values,
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

    #[Override]
    public function visitProgram(Ast\Program $program)
    {
        /** @var Ast\Stmt $statement */
        foreach ($program->statements as $statement) {
            $statement->accept($this);
        }
    }

    #[Override]
    public function visitIfStatement(Ast\IfStatement $stmt): void
    {
        $condType = $this->toBool(
            $stmt->condition->accept($this),
            fn() => $this->exprToken($stmt->condition)
        );

        $stmt->then->accept($this);

        if ($stmt->else !== null) {
            $stmt->else->accept($this);
        }
    }

    #[Override]
    public function visitForStatement(Ast\ForStatement $stmt): void
    {
        $enclosingEnv = $this->environment;
        $this->environment = new Environment($enclosingEnv);

        try {
            if ($stmt->setup !== null) {
                $stmt->setup->accept($this);
            }

            if ($stmt->test !== null) {
                $testType = $stmt->test->accept($this);

                if ($testType instanceof Types\Chiamabile) {
                    $token = $this->exprToken($stmt->test);
                    throw new TypeError(
                        "'{$token->lexeme}' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?",
                        $token->loc
                    );
                }
            }

            if ($stmt->update !== null) {
                $stmt->update->accept($this);
            }

            $stmt->body->accept($this);
        } finally {
            $this->environment = $enclosingEnv;
        }
    }

    #[Override]
    public function visitBlock(Ast\Block $stmt)
    {
        $enclosingEnv = $this->environment;
        $this->environment = new Environment($enclosingEnv);

        /** @var Ast\Stmt $statement */
        foreach ($stmt->statements as $statement) {
            $statement->accept($this);
        }

        $this->environment = $enclosingEnv;
    }

    #[Override]
    public function visitExprStatement(Ast\ExprStatement $stmt): Types\Type
    {
        return $stmt->expr->accept($this);
    }

    #[Override]
    public function visitCallExpr(Ast\CallExpr $expr): Types\Type
    {
        $fn = $this->environment->get($expr->callee);

        if (!($fn instanceof Types\Chiamabile)) {
            throw new RuntimeError(
                "Atteso che '{$expr->callee->lexeme}' fosse una funzione.",
                $expr->callee->loc
            );
        }

        foreach ($expr->args as $arg) {
            $arg->accept($this);
        }

        /** @var Types\Chiamabile $fn */
        return $fn->returnType;
    }

    #[Override]
    public function visitVarDeclExpr(Ast\VarDeclExpr $expr): Types\Type
    {
        $type = $expr->value->accept($this);
        $this->environment->define($expr->identifier, $type);

        return $type;
    }

    #[Override]
    public function visitConstDeclExpr(Ast\ConstDeclExpr $expr): Types\Type
    {
        $type = $expr->value->accept($this);
        $this->environment->defineConst($expr->identifier, $type);

        return $type;
    }

    #[Override]
    public function visitAssignExpr(Ast\AssignExpr $expr): Types\Type
    {
        $type = $expr->value->accept($this);
        $this->environment->assign($expr->identifier, $type);

        return $type;
    }

    #[Override]
    public function visitStringLiteral(Ast\StringLiteral $expr): Types\Type
    {
        return new Types\Stringa($expr->token->lexeme);
    }

    #[Override]
    public function visitIdentifier(Ast\Identifier $expr): Types\Type
    {
        return $this->environment->get($expr->token);
    }

    #[Override]
    public function visitNullLiteral(Ast\NullLiteral $expr): Types\Type
    {
        return new Types\Nullo();
    }

    #[Override]
    public function visitBoolLiteral(Ast\BoolLiteral $expr): Types\Type
    {
        return new Types\Booleano($expr->token->lexeme === 'vero');
    }

    #[Override]
    public function visitIntLiteral(Ast\IntLiteral $expr): Types\Type
    {
        return new Types\Intero($expr->token->lexeme);
    }

    #[Override]
    public function visitFloatLiteral(Ast\FloatLiteral $expr): Types\Type
    {
        return new Types\Decimale($expr->token->lexeme);
    }

    #[Override]
    public function visitPostfixExpr(Ast\PostfixExpr $expr)
    {
        $op = $expr->operator;

        $currentType = $expr->lvalue->accept($this);

        if (!$expr->lvalue instanceof Ast\Identifier) {
            $operation = $op->type === TokenType::INCREMENT
                ? 'incremento'
                : 'decremento';
            throw new RuntimeError(
                "Richiesto un lvalue come operando di {$operation}.",
                $op->loc
            );
        }

        $this->expectType($op, $currentType, [Types\Intero::class, Types\Decimale::class]);
        $this->environment->assign($expr->lvalue->token, $currentType);

        return $currentType;
    }

    #[Override]
    public function visitUnaryExpr(Ast\UnaryExpr $expr): Types\Type
    {
        $op = $expr->op;
        $right = $expr->right->accept($this);

        switch ($op->type) {
            case TokenType::PLUS:
            case TokenType::MINUS:
                $this->expectType($op, $right, [Types\Intero::class, Types\Decimale::class]);
                return $right;

            case TokenType::INCREMENT:
            case TokenType::DECREMENT:
                if (!$expr->right instanceof Ast\Identifier) {
                    $operation = $op->type === TokenType::INCREMENT
                        ? 'incremento'
                        : 'decremento';
                    throw new RuntimeError(
                        "Richiesto un lvalue come operando di {$operation}.",
                        $op->loc
                    );
                }

                $this->expectType($op, $right, [Types\Intero::class, Types\Decimale::class]);
                $this->environment->assign($expr->right->token, $right);

                return $right;

            default:
                throw new Exception("Operatore unario '{$op->lexeme}' non implementato.\n");
        }
    }

    #[Override]
    public function visitBinaryExpr(Ast\BinaryExpr $expr): Types\Type
    {
        $op = $expr->op;

        if ($op->type === TokenType::AND) {
            $leftBool = $this->toBool(
                $expr->left->accept($this),
                fn() => $this->exprToken($expr->left)
            );

            $this->toBool(
                $expr->right->accept($this),
                fn() => $this->exprToken($expr->right)
            );

            return $leftBool;
        }

        if ($op->type === TokenType::OR) {
            $leftBool = $this->toBool(
                $expr->left->accept($this),
                fn() => $this->exprToken($expr->left)
            );

            $this->toBool(
                $expr->right->accept($this),
                fn() => $this->exprToken($expr->right)
            );

            return $leftBool;
        }

        $left = $expr->left->accept($this);
        $right = $expr->right->accept($this);

        switch ($op->type) {
            case TokenType::PLUS:
                $this->expectType(
                    $op,
                    [$left, $right],
                    [Types\Intero::class, Types\Decimale::class, Types\Stringa::class]
                );

                if (Types\Stringa::is($left) || Types\Stringa::is($right)) {
                    return new Types\Stringa("");
                }

                return $this->toCodiceType($left->value + $right->value);

            case TokenType::MINUS:
                $this->expectType($op, [$left, $right], [Types\Intero::class, Types\Decimale::class]);

                /** @var Types\Intero|Types\Decimale $left */
                /** @var Types\Intero|Types\Decimale $right */

                return $this->toCodiceType($left->value - $right->value);

            case TokenType::STAR:
                $this->expectType($op, [$left, $right], [Types\Intero::class, Types\Decimale::class, Types\Stringa::class]);

                /** @var Types\Intero|Types\Decimale|Types\Stringa $left */
                /** @var Types\Intero|Types\Decimale|Types\Stringa $right */

                // string x string
                if (Types\Stringa::is($left) && Types\Stringa::is($right)) {
                    throw new TypeError("Operatore '*' non applicabile a due stringhe.", $op->loc);
                }

                // string x float | float x string - invalid
                if ((Types\Stringa::is($left) && Types\Decimale::is($right)) || (Types\Decimale::is($left) && Types\Stringa::is($right))) {
                    throw new TypeError(
                        "Operatore '*' non applicabile tra {$this->typeName($left)} e {$this->typeName($right)}.",
                        $op->loc
                    );
                }

                // string x int | int x string
                if (Types\Stringa::is($left) || Types\Stringa::is($right)) {
                    $times = Types\Stringa::is($left) ? $right->value : $left->value;

                    if ($times < 0) {
                        throw new TypeError("Il moltiplicatore della stringa non può essere negativo.", $op->loc);
                    }

                    return new Types\Stringa("");
                }

                return $this->toCodiceType($left->value * $right->value);

            case TokenType::SLASH:
                $this->expectType($op, [$left, $right], [Types\Intero::class, Types\Decimale::class]);

                /** @var Types\Intero|Types\Decimale $left */
                /** @var Types\Intero|Types\Decimale $right */

                if ($right->value == 0) {
                    throw new DivisionByZeroError("Impossibile dividere per zero.", $op->loc);
                }

                return new Types\Decimale((float) $left->value / (float) $right->value);

            case TokenType::LESS:
            case TokenType::GREATER:
            case TokenType::GREATER_EQUAL:
            case TokenType::LESS_EQUAL:
                $this->expectType($op, [$left, $right], [Types\Intero::class, Types\Decimale::class, Types\Stringa::class]);

                if (Types\Stringa::is($left) xor Types\Stringa::is($right)) {
                    throw new TypeError(
                        "Operatore '{$op->lexeme}' non applicabile tra {$this->typeName($left)} e {$this->typeName($right)}.",
                        $op->loc
                    );
                }

                /** @var Types\Intero|Types\Decimale|Types\Stringa $left */
                /** @var Types\Intero|Types\Decimale|Types\Stringa $right */
                return $this->toCodiceType(match ($op->type) {
                    TokenType::LESS => $left->value < $right->value,
                    TokenType::GREATER => $left->value > $right->value,
                    TokenType::LESS_EQUAL => $left->value <= $right->value,
                    TokenType::GREATER_EQUAL => $left->value >= $right->value,
                });

            case TokenType::EQUAL:
            case TokenType::NOT_EQUAL:
                /** @var Types\Intero|Types\Decimale|Types\Stringa|Types\Chiamabile|Types\Nullo|Types\Booleano $left */
                /** @var Types\Intero|Types\Decimale|Types\Stringa|Types\Chiamabile|Types\Nullo|Types\Booleano $right */

                $equal = match (true) {
                    Types\Nullo::is($left) && Types\Nullo::is($right) => true,
                    Types\Chiamabile::is($left) || Types\Chiamabile::is($right) => $left === $right,
                    get_class($left) !== get_class($right) => false,
                    default => $left->value === $right->value,
                };

                return $this->toCodiceType($op->type === TokenType::EQUAL ? $equal : !$equal);

            case TokenType::MOD:
                $this->expectType($op, [$left, $right], Types\Intero::class);

                /** @var Types\Intero $left */
                /** @var Types\Intero $right */

                if ($right->value === 0) {
                    throw new DivisionByZeroError(
                        "Impossibile calcolare il resto della divisione per zero.",
                        $op->loc
                    );
                }

                return new Types\Intero($left->value % $right->value);

            default:
                throw new Exception("Operatore binario '{$op->lexeme}' non implementato.\n");
        }
    }
}
