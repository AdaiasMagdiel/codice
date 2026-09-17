<?php

namespace App\Visitors;

use App\Ast;
use App\Enums\Operation;
use App\Enums\TokenType;
use App\Enums\VMType;
use Exception;
use Override;

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
    private array $symbols  = [];
    private int $symbolsPos = 0;

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
                VMType::INT => $this->uint_32((int) $key),
                VMType::STR => $this->uint_32(strlen($key)) . $this->string($key),
                default => throw new Exception("Not implemented: " . $item[1]->name),
            };

            $this->header .= $this->uint_8($item[1]->value);
            $this->header .= $value;
        }

        $this->header .= $this->uint_16(count($this->symbols));

        file_put_contents(
            $this->outputFile,
            $this->header . $this->buffer
        );
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

        if (!array_key_exists($key, $this->symbols)) {
            $this->symbols[$key] = $this->symbolsPos++;
        }

        $this->addInstruction(Operation::STORE_LOCAL);
        $this->buffer .= $this->uint_16($this->symbols[$key]);
    }

    #[Override]
    public function visitExprStatement(Ast\ExprStatement $stmt)
    {
        return $stmt->expr->accept($this);
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
    public function visitStringLiteral(Ast\StringLiteral $expr)
    {
        $this->addToPool($expr->token->lexeme, VMType::STR);
    }

    #[Override]
    public function visitIdentifier(Ast\Identifier $expr)
    {
        $varName = $expr->token->lexeme;

        if (array_key_exists($varName, $this->symbols)) {
            $slot = $this->symbols[$varName];
            $this->addInstruction(Operation::LOAD_LOCAL);
            $this->buffer .= $this->uint_16($slot);
            return;
        }

        $this->addToPool($varName, VMType::STR);
    }

    public function visitAssignExpr(Ast\AssignExpr $expr) {}
    public function visitBlock(Ast\Block $stmt) {}
    public function visitBoolLiteral(Ast\BoolLiteral $expr) {}
    public function visitConstDeclExpr(Ast\ConstDeclExpr $expr) {}
    public function visitFloatLiteral(Ast\FloatLiteral $expr) {}
    public function visitForStatement(Ast\ForStatement $stmt) {}
    public function visitIfStatement(Ast\IfStatement $stmt) {}
    public function visitNullLiteral(Ast\NullLiteral $expr) {}
    public function visitPostfixExpr(Ast\PostfixExpr $expr) {}
    public function visitUnaryExpr(Ast\UnaryExpr $expr) {}
}
