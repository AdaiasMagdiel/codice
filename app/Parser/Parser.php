<?php

namespace App\Parser;

use App\Ast\AssignExpr;
use App\Ast\BinaryExpr;
use App\Ast\BoolLiteral;
use App\Ast\CallExpr;
use App\Ast\ExprStatement;
use App\Ast\FloatLiteral;
use App\Enums\TokenType;
use App\Interfaces\Expr;
use App\Interfaces\Stmt;
use App\Lexer\Token;
use App\Ast\Program;
use App\Ast\StringLiteral;
use App\Ast\Identifier;
use App\Ast\IntLiteral;
use App\Ast\NullLiteral;
use App\Ast\UnaryExpr;
use App\Ast\VarDeclExpr;
use App\Exceptions\ParseError;

class Parser
{
    private int $pos = 0;
    /** @var Token[] */
    private array $tokens = [];
    private int $tokensCount = 0;

    public function init(array $tokens = [])
    {
        $this->pos = 0;
        $this->tokens = $tokens;
        $this->tokensCount = count($tokens);
    }

    private function isAtEnd(): bool
    {
        return $this->pos >= $this->tokensCount || $this->tokens[$this->pos]->type === TokenType::EOF;
    }

    private function peek(int $offset = 0): Token
    {
        if ($this->isAtEnd()) {
            $loc = $this->tokens[$this->tokensCount - 1]->loc;
            return new Token(TokenType::EOF, null, $loc);
        }

        return $this->tokens[$this->pos + $offset];
    }

    private function consume(): Token
    {
        $token = $this->peek();
        $this->pos++;

        return $token;
    }

    private function check(TokenType $type, int $offset = 0): bool
    {
        return $this->peek($offset)->type === $type;
    }

    private function expect(TokenType $type): Token
    {
        if ($this->check($type)) {
            return $this->consume();
        } else {
            throw new ParseError(
                "Atteso '{$type->name}', ma è stato trovato '{$this->peek()->type->name}'.",
                $this->peek()->loc
            );
        }
    }

    public function parse(): Program
    {
        $statements = [];

        while (!$this->isAtEnd()) {
            $statements[] = $this->parseStatement();
        }

        return new Program($statements);
    }

    private function parseStatement(): Stmt
    {
        return $this->parseExprStatement();
    }

    private function parseExprStatement(): Stmt
    {
        $expr = $this->parseExpression();
        $this->expect(TokenType::SEMICOLON);

        return new ExprStatement($expr);
    }

    private function parseExpression(): Expr
    {
        if ($this->check(TokenType::SIA)) {
            return $this->parseVarDeclExpr();
        }

        if (
            $this->check(TokenType::IDENTIFIER) &&
            $this->check(TokenType::ASSIGN, 1)
        ) {
            return $this->parseAssignExpr();
        }

        return $this->parseAdditiveExpression();
    }

    private function parseVarDeclExpr(): Expr
    {
        $this->expect(TokenType::SIA);

        $identifier = $this->expect(TokenType::IDENTIFIER);
        $value = new NullLiteral(new Token(TokenType::NULL, "nullo", $identifier->loc));

        if ($this->check(TokenType::ASSIGN)) {
            $this->consume();
            $value = $this->parseExpression();
        }

        return new VarDeclExpr($identifier, $value);
    }

    private function parseAssignExpr(): Expr
    {
        $identifier = $this->expect(TokenType::IDENTIFIER);
        $this->expect(TokenType::ASSIGN);
        $value = $this->parseExpression();

        return new AssignExpr($identifier, $value);
    }

    private function parseAdditiveExpression(): Expr
    {
        $expr = $this->parseMultiplicativeExpression();

        while ($this->check(TokenType::PLUS) || $this->check(TokenType::MINUS)) {
            $op = $this->consume();
            $right = $this->parseMultiplicativeExpression();

            $expr = new BinaryExpr($expr, $op, $right);
        }

        return $expr;
    }

    private function parseMultiplicativeExpression(): Expr
    {
        $expr = $this->parseUnaryExpression();

        while ($this->check(TokenType::STAR) || $this->check(TokenType::SLASH)) {
            $op = $this->consume();
            $right = $this->parseUnaryExpression();

            $expr = new BinaryExpr($expr, $op, $right);
        }

        return $expr;
    }

    private function parseUnaryExpression(): Expr
    {
        if ($this->check(TokenType::PLUS) || $this->check(TokenType::MINUS)) {
            $op = $this->consume();
            $right = $this->parsePrimaryExpression();

            return new UnaryExpr($op, $right);
        }

        return $this->parsePrimaryExpression();
    }

    private function parsePrimaryExpression(): Expr
    {
        if ($this->check(TokenType::STRING)) {
            return new StringLiteral($this->consume());
        }

        if ($this->check(TokenType::IDENTIFIER)) {
            $token = $this->consume();

            if ($this->peek()->type === TokenType::LEFT_PAREN) {
                return $this->parseCallExpr($token);
            }

            return new Identifier($token);
        }

        if ($this->check(TokenType::NULL)) {
            return new NullLiteral($this->consume());
        }

        if ($this->check(TokenType::BOOL)) {
            return new BoolLiteral($this->consume());
        }

        if ($this->check(TokenType::INT)) {
            return new IntLiteral($this->consume());
        }

        if ($this->check(TokenType::FLOAT)) {
            return new FloatLiteral($this->consume());
        }

        if ($this->check(TokenType::LEFT_PAREN)) {
            $this->consume();

            $expr = $this->parseExpression();
            $this->expect(TokenType::RIGHT_PAREN);

            return $expr;
        }

        throw new ParseError(
            "Atteso un valore.",
            $this->peek()->loc
        );
    }

    private function parseCallExpr(Token $identifier): CallExpr
    {
        $this->expect(TokenType::LEFT_PAREN);

        $arguments = [];
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            $arguments = $this->parseArgumentList();
        }

        $this->expect(TokenType::RIGHT_PAREN);

        return new CallExpr($identifier, $arguments, $identifier->loc);
    }

    private function parseArgumentList(): array
    {
        $arguments = [$this->parseExpression()];

        while ($this->check(TokenType::COMMA)) {
            $this->consume();
            $arguments[] = $this->parseExpression();
        }

        return $arguments;
    }
}
