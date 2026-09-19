<?php

namespace App\Visitors;

use App\Ast;
use App\Enums\Operation;
use App\Enums\TokenType;
use App\Enums\VMType;
use App\Exceptions\RuntimeError;
use Exception;
use Override;

define('MAGIC_SIGNATURE', 0xADA1A5); // 24 bits
define('BYTECODE_VERSION', 0x01);   // 8 bits

// 0xADA1A5 << 8 = 0xADA1A500
// 0xADA1A500 | 0x01 = 0xADA1A501
define('MAGIC_NUMBER', (MAGIC_SIGNATURE << 8) | BYTECODE_VERSION);

class BytecodeCompiler implements Visitor
{
    private string $header  = '';
    private string $buffer  = '';

    private array $pool     = [];
    private int $poolPos    = 0;

    private array $symbolTable  = [];
    private int $localCount = 0;
    private int $maxLocals = 0;
    private int $depth = 0;

    public function __construct(
        private string $outputFile
    ) {}

    private function uint_8(int $value): string
    {
        return pack("C", $value);
    }

    private function uint_16(int $value): string
    {
        return pack("n", $value);
    }

    private function uint_32(int $value): string
    {
        return pack("N", $value);
    }

    private function string(string $value): string
    {
        return pack("a*", $value);
    }

    private function findSymbol(string $key)
    {
        foreach ($this->symbolTable as $symbol) {
            if ($symbol["name"] === $key && $symbol["depth"] === $this->depth) {
                return $symbol;
            }
        }
        return null;
    }

    private function addInstruction(Operation $op)
    {
        $this->buffer .= $this->uint_8($op->value);
    }

    private function addToPool(mixed $value, VMType $type): void
    {
        if (!array_key_exists($value, $this->pool)) {
            $this->pool[(string) $value] = [$this->poolPos++, $type];
        }

        $this->addInstruction(Operation::LOAD_CONST);
        $this->buffer .= $this->uint_16($this->pool[(string) $value][0]);
    }

    #[Override]
    public function visitProgram(Ast\Program $program)
    {
        $this->header .= $this->uint_32(MAGIC_NUMBER);

        /** @var Ast\Stmt $statement */
        foreach ($program->statements as $statement) {
            $statement->accept($this);
        }

        $this->header .= $this->uint_16(count($this->pool));

        uasort($this->pool, function ($a, $b) {
            return $a[0] <=> $b[0];
        });

        foreach ($this->pool as $key => $item) {
            $value = match ($item[1]) {
                VMType::INT   => $this->uint_32((int) $key),
                VMType::STR   => $this->uint_32(strlen($key)) . $this->string($key),
                VMType::FLOAT => pack('E', (float) $key),
                default       => throw new Exception("Not implemented: " . $item[1]->name),
            };

            $this->header .= $this->uint_8($item[1]->value);
            $this->header .= $value;
        }

        $this->header .= $this->uint_16($this->maxLocals);

        file_put_contents(
            $this->outputFile,
            $this->header . $this->buffer
        );
    }

    public function visitIfStatement(Ast\IfStatement $stmt)
    {
        $stmt->condition->accept($this);

        $this->addInstruction(Operation::JUMP_IF_FALSE);
        $ifPos = strlen($this->buffer);
        $this->buffer .= $this->uint_16(0x0000);

        $stmt->then->accept($this);

        $offset = strlen($this->buffer);
        $this->buffer = substr_replace(
            $this->buffer,
            $this->uint_16($offset),
            $ifPos,
            2
        );

        if (!is_null($stmt->else)) {
            $this->addInstruction(Operation::JUMP);
            $elsePos = strlen($this->buffer);
            $this->buffer .= $this->uint_16(0x0000);

            $this->buffer = substr_replace(
                $this->buffer,
                $this->uint_16($elsePos + 2),
                $ifPos,
                2
            );

            $stmt->else->accept($this);

            $offset = strlen($this->buffer);
            $this->buffer = substr_replace(
                $this->buffer,
                $this->uint_16($offset),
                $elsePos,
                2
            );
        }
    }

    #[Override]
    public function visitBlock(Ast\Block $stmt)
    {
        $localCount = $this->localCount;
        $symbolCount = count($this->symbolTable); // Snapshot do tamanho da tabela

        $this->depth++;
        foreach ($stmt->statements as $statement) {
            $statement->accept($this);
        }
        $this->depth--;

        $this->maxLocals = max($this->maxLocals, $this->localCount);
        $this->localCount = $localCount;

        $this->symbolTable = array_slice($this->symbolTable, 0, $symbolCount);
    }

    #[Override]
    public function visitCallExpr(Ast\CallExpr $expr)
    {
        $count = count($expr->args);

        for ($i = $count - 1; $i >= 0; $i--) {
            $expr->args[$i]->accept($this);
        }

        $this->addToPool($count, VMType::INT);
        $this->addToPool($expr->callee->lexeme, VMType::STR);

        $this->addInstruction(Operation::CALL_FUNC);
    }

    #[Override]
    public function visitVarDeclExpr(Ast\VarDeclExpr $expr)
    {
        $key = $expr->identifier->lexeme;
        $expr->value->accept($this);

        $symbol = $this->findSymbol($key);
        if (is_null($symbol)) {
            $symbol = [
                "name" => $key,
                "slot" => $this->localCount++,
                "depth" => $this->depth
            ];
            $this->symbolTable[] = $symbol;
        } else {
            throw new RuntimeError(
                "L'identificatore '{$expr->identifier->lexeme}' è già stato dichiarato.",
                $expr->identifier->loc
            );
        }

        $this->addInstruction(Operation::STORE_LOCAL);
        $this->buffer .= $this->uint_16($symbol["slot"]);
    }

    #[Override]
    public function visitExprStatement(Ast\ExprStatement $stmt)
    {
        $stmt->expr->accept($this);
    }

    #[Override]
    public function visitBinaryExpr(Ast\BinaryExpr $expr)
    {
        // Constant folding: evaluate string repetition at compile time
        if ($expr->op->type === TokenType::STAR) {
            if (
                ($expr->left instanceof Ast\StringLiteral && $expr->right instanceof Ast\IntLiteral) ||
                ($expr->left instanceof Ast\IntLiteral && $expr->right instanceof Ast\StringLiteral)
            ) {
                [$str, $times] = ($expr->left instanceof Ast\StringLiteral)
                    ? [$expr->left->token->lexeme, $expr->right->token->lexeme]
                    : [$expr->right->token->lexeme, $expr->left->token->lexeme];

                $this->addToPool(str_repeat($str, (int) $times), VMType::STR);
                return;
            }
        }

        $expr->left->accept($this);
        $expr->right->accept($this);

        $opcode = match ($expr->op->type) {
            TokenType::PLUS  => Operation::ADD,
            TokenType::MINUS => Operation::SUB,
            TokenType::STAR  => Operation::MULT,
            TokenType::SLASH => Operation::DIV,
        };
        $this->addInstruction($opcode);
    }

    #[Override]
    public function visitIntLiteral(Ast\IntLiteral $expr)
    {
        $this->addToPool($expr->token->lexeme, VMType::INT);
    }

    #[Override]
    public function visitFloatLiteral(Ast\FloatLiteral $expr)
    {
        $this->addToPool($expr->token->lexeme, VMType::FLOAT);
    }

    #[Override]
    public function visitStringLiteral(Ast\StringLiteral $expr)
    {
        $this->addToPool($expr->token->lexeme, VMType::STR);
    }

    #[Override]
    public function visitBoolLiteral(Ast\BoolLiteral $expr)
    {
        $this->addInstruction(
            $expr->token->lexeme === 'vero'
                ? Operation::PUSH_TRUE
                : Operation::PUSH_FALSE
        );
    }

    public function visitNullLiteral(Ast\NullLiteral $expr)
    {
        $this->addInstruction(Operation::PUSH_NULL);
    }

    #[Override]
    public function visitIdentifier(Ast\Identifier $expr)
    {
        $varName = $expr->token->lexeme;

        $symbol = $this->findSymbol($varName);
        if (!is_null($symbol)) {
            $slot = $this->symbolTable[$varName]["slot"];
            $this->addInstruction(Operation::LOAD_LOCAL);
            $this->buffer .= $this->uint_16($slot);
            return;
        }

        $this->addToPool($varName, VMType::STR);
    }

    public function visitAssignExpr(Ast\AssignExpr $expr) {}
    public function visitConstDeclExpr(Ast\ConstDeclExpr $expr) {}
    public function visitForStatement(Ast\ForStatement $stmt) {}
    public function visitPostfixExpr(Ast\PostfixExpr $expr) {}
    public function visitUnaryExpr(Ast\UnaryExpr $expr) {}
}
