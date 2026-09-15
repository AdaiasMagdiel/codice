<?php

namespace App\Parser;

use App\Ast\AssignExpr;
use App\Ast\BinaryExpr;
use App\Ast\Block;
use App\Ast\BoolLiteral;
use App\Ast\CallExpr;
use App\Ast\ConstDeclExpr;
use App\Ast\ExprStatement;
use App\Ast\FloatLiteral;
use App\Ast\ForStatement;
use App\Enums\TokenType;
use App\Ast\Expr;
use App\Ast\Stmt;
use App\Lexer\Token;
use App\Ast\Program;
use App\Ast\StringLiteral;
use App\Ast\Identifier;
use App\Ast\IfStatement;
use App\Ast\IntLiteral;
use App\Ast\NullLiteral;
use App\Ast\PostfixExpr;
use App\Ast\UnaryExpr;
use App\Ast\VarDeclExpr;
use App\Exceptions\ParseError;
use Exception;

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
        if ($this->check(TokenType::SE)) {
            return $this->parseIfStatement();
        }

        if ($this->check(TokenType::PER)) {
            return $this->parseForStatement();
        }

        if ($this->check(TokenType::ALTRIMENTI)) {
            throw new ParseError(
                "'altrimenti' senza un 'se' corrispondente.",
                $this->peek()->loc
            );
        }

        if ($this->check(TokenType::LEFT_BRACE)) {
            return $this->parseBlock();
        }

        return $this->parseExprStatement();
    }

    private function parseIfStatement(): Stmt
    {
        $this->expect(TokenType::SE);

        $this->expect(TokenType::LEFT_PAREN);
        $expr = $this->parseExpression();
        $this->expect(TokenType::RIGHT_PAREN);

        $then = $this->parseBlock();

        $else = null;
        if ($this->check(TokenType::ALTRIMENTI)) {
            $this->consume();

            $else = $this->check(TokenType::SE)
                ? $this->parseIfStatement()
                : $this->parseBlock();
        }

        return new IfStatement($expr, $then, $else);
    }

    private function parseForStatement(): Stmt
    {
        $this->expect(TokenType::PER);
        $this->expect(TokenType::LEFT_PAREN);

        $setup = new NullLiteral(new Token(TokenType::NULL, "nullo", $this->peek()->loc));
        if (!$this->check(TokenType::SEMICOLON)) {
            $setup = $this->parseExpression();
        }
        $this->expect(TokenType::SEMICOLON);

        $test = new BoolLiteral(new Token(TokenType::BOOL, "vero", $this->peek()->loc));
        if (!$this->check(TokenType::SEMICOLON)) {
            $test = $this->parseExpression();
        }
        $this->expect(TokenType::SEMICOLON);

        $update = new NullLiteral(new Token(TokenType::NULL, "nullo", $this->peek()->loc));
        if (!$this->check(TokenType::RIGHT_PAREN)) {
            $update = $this->parseExpression();
        }
        $this->expect(TokenType::RIGHT_PAREN);

        $body = $this->parseBlock();

        return new ForStatement(
            $setup,
            $test,
            $update,
            $body
        );
    }

    private function parseBlock(): Stmt
    {
        $statements = [];
        $this->expect(TokenType::LEFT_BRACE);
        while (!$this->check(TokenType::RIGHT_BRACE) && !$this->isAtEnd()) {
            $statements[] = $this->parseStatement();
        }
        $this->expect(TokenType::RIGHT_BRACE);

        return new Block($statements);
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

        if ($this->check(TokenType::COST)) {
            return $this->parseConstDeclExpr();
        }

        if (
            $this->check(TokenType::IDENTIFIER) &&
            $this->check(TokenType::ASSIGN, 1)
        ) {
            return $this->parseAssignExpr();
        }

        return $this->parseLogicalOrExpr();
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

    private function parseConstDeclExpr(): Expr
    {
        $this->expect(TokenType::COST);

        $identifier = $this->expect(TokenType::IDENTIFIER);
        $this->expect(TokenType::ASSIGN);
        $value = $this->parseExpression();

        return new ConstDeclExpr($identifier, $value);
    }

    private function parseAssignExpr(): Expr
    {
        $identifier = $this->expect(TokenType::IDENTIFIER);
        $this->expect(TokenType::ASSIGN);
        $value = $this->parseExpression();

        return new AssignExpr($identifier, $value);
    }

    private function parseLogicalOrExpr(): Expr
    {
        $expr = $this->parseLogicalAndExpr();

        if ($this->check(TokenType::OR)) {
            $expr = new BinaryExpr(
                $expr,
                $this->consume(),
                $this->parseLogicalAndExpr()
            );
        }

        return $expr;
    }

    private function parseLogicalAndExpr(): Expr
    {
        $expr = $this->parseEqualityExpr();

        if ($this->check(TokenType::AND)) {
            $expr = new BinaryExpr(
                $expr,
                $this->consume(),
                $this->parseEqualityExpr()
            );
        }

        return $expr;
    }

    private function parseEqualityExpr(): Expr
    {
        $expr = $this->parseRelationalExpr();

        if (
            $this->check(TokenType::EQUAL) ||
            $this->check(TokenType::NOT_EQUAL)
        ) {
            $expr = new BinaryExpr(
                $expr,
                $this->consume(),
                $this->parseRelationalExpr()
            );
        }

        return $expr;
    }

    private function parseRelationalExpr(): Expr
    {
        $expr = $this->parseAdditiveExpression();

        if (
            $this->check(TokenType::LESS)       ||
            $this->check(TokenType::LESS_EQUAL) ||
            $this->check(TokenType::GREATER)    ||
            $this->check(TokenType::GREATER_EQUAL)
        ) {
            $expr = new BinaryExpr(
                $expr,
                $this->consume(),
                $this->parseAdditiveExpression()
            );
        }

        return $expr;
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

        while (
            $this->check(TokenType::STAR)  ||
            $this->check(TokenType::SLASH) ||
            $this->check(TokenType::MOD)
        ) {
            $op = $this->consume();
            $right = $this->parseUnaryExpression();

            $expr = new BinaryExpr($expr, $op, $right);
        }

        return $expr;
    }

    private function parseUnaryExpression(): Expr
    {
        if (
            $this->check(TokenType::PLUS)      ||
            $this->check(TokenType::MINUS)     ||
            $this->check(TokenType::INCREMENT) ||
            $this->check(TokenType::DECREMENT)
        ) {
            $op = $this->consume();
            $right = $this->parseUnaryExpression();

            return new UnaryExpr($op, $right);
        }

        return $this->parsePostfixExpression();
    }

    private function parsePostfixExpression(): Expr
    {
        $expr = $this->parsePrimaryExpression();

        if (
            $this->check(TokenType::INCREMENT) ||
            $this->check(TokenType::DECREMENT)
        ) {
            return new PostfixExpr($expr, $this->consume());
        }

        return $expr;
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

        return new CallExpr($identifier, $arguments);
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
