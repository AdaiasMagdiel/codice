<?php

namespace App\Visitors;

use App\Ast;
use App\Enums\TokenType;
use App\Runtime\Environment;
use Exception;
use Override;

define('MAGIC_SIGNATURE', 0xADA1A5); // 24 bits
define('BYTECODE_VERSION', 0x01);   // 8 bits

// 0xADA1A5 << 8 = 0xADA1A500
// 0xADA1A500 | 0x01 = 0xADA1A501
define('MAGIC_NUMBER', (MAGIC_SIGNATURE << 8) | BYTECODE_VERSION);

enum TYPE: int
{
    case INT = 0x01;
    case STR = 0x02;
}

enum OP: int
{
    case LOAD_CONST = 0x01;
    case PUSH       = 0x02;
    case ADD        = 0x03;
    case CALL_FUNC  = 0x04;
}

class ByteCode implements Visitor
{
    private string $header = '';
    private string $buffer = '';
    private array $pool    = [];
    private int $poolPos   = 0;

    public function __construct(
        private Environment $environment,
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

    private function c_string(string $value): string
    {
        return pack("a*", $value . "\0");
    }

    private function addInstruction(OP $op)
    {
        $this->buffer .= $this->uint_8($op->value);
    }

    private function addToPool(mixed $value, TYPE $type): void
    {
        if (!array_key_exists($value, $this->pool)) {
            $this->pool[(string) $value] = [$this->poolPos++, $type];
        }

        $this->addInstruction(OP::LOAD_CONST);
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
                TYPE::INT => $this->uint_32((int) $key),
                TYPE::STR => $this->uint_32(strlen($key)) . $this->c_string($key),
                default => throw new Exception("Not implemented: " . $item[1]->name),
            };

            $this->header .= $this->uint_8($item[1]->value);
            $this->header .= $value;
        }

        file_put_contents($this->outputFile, $this->header . $this->buffer);
    }

    #[Override]
    public function visitCallExpr(Ast\CallExpr $expr)
    {
        $count = count($expr->args);

        for ($i = $count - 1; $i >= 0; $i--) {
            $expr->args[$i]->accept($this);
        }

        $this->addToPool($count, TYPE::INT);
        $this->addToPool($expr->callee->lexeme, TYPE::STR);

        $this->addInstruction(OP::CALL_FUNC);
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
                $this->addInstruction(OP::ADD);
                break;
        }
    }

    #[Override]
    public function visitIntLiteral(Ast\IntLiteral $expr)
    {
        $this->addToPool($expr->token->lexeme, TYPE::INT);
    }

    #[Override]
    public function visitStringLiteral(Ast\StringLiteral $expr)
    {
        $this->addToPool($expr->token->lexeme, TYPE::STR);
    }

    #[Override]
    public function visitIdentifier(Ast\Identifier $expr)
    {
        $this->addToPool($expr->token->lexeme, TYPE::STR);
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
    public function visitVarDeclExpr(Ast\VarDeclExpr $expr) {}
}
