<?php

namespace App\Visitors;

use App\Ast;
use App\Types;
use App\Enums\Operation;
use App\Enums\TokenType;
use App\Enums\VMType;
use App\Exceptions\RuntimeError;
use Exception;
use Override;
use TypeError;

define('MAGIC_SIGNATURE', 0xADA1A5); // 24 bits
define('BYTECODE_VERSION', 0x01);   // 8 bits

// 0xADA1A5 << 8 = 0xADA1A500
// 0xADA1A500 | 0x01 = 0xADA1A501
define('MAGIC_NUMBER', (MAGIC_SIGNATURE << 8) | BYTECODE_VERSION);

class ByteCode implements Visitor
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

        $stmt->then->accept($this);

        if (!is_null($stmt->else)) {
            $stmt->else->accept($this);
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
        $op = $expr->op;

        $expr->left->accept($this);
        $expr->right->accept($this);

        switch ($op->type) {
            case TokenType::PLUS:
                $this->addInstruction(Operation::ADD);
                break;
        }
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
