<?php

use App\Ast\AssignExpr;
use App\Ast\BinaryExpr;
use App\Ast\Block;
use App\Ast\BoolLiteral;
use App\Ast\CallExpr;
use App\Ast\ConstDeclExpr;
use App\Ast\ExprStatement;
use App\Ast\FloatLiteral;
use App\Ast\ForStatement;
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
use App\Visitors\Visitor;
use App\Lexer\Loc;
use App\Lexer\Token;

function makeAstToken(TokenType $type = TokenType::IDENTIFIER, mixed $lexeme = 'x'): Token
{
    return new Token($type, $lexeme, new Loc());
}

function makeAstVisitorSpy(): object
{
    return new class implements Visitor {
        public array $calls = [];

        private function record(string $method, object $node): void
        {
            $this->calls[] = [$method, $node];
        }

        public function visitAssignExpr($expr)
        {
            $this->record('visitAssignExpr', $expr);
        }
        public function visitBinaryExpr($expr)
        {
            $this->record('visitBinaryExpr', $expr);
        }
        public function visitBlock($stmt)
        {
            $this->record('visitBlock', $stmt);
        }
        public function visitBoolLiteral($expr)
        {
            $this->record('visitBoolLiteral', $expr);
        }
        public function visitCallExpr($expr)
        {
            $this->record('visitCallExpr', $expr);
        }
        public function visitConstDeclExpr($expr)
        {
            $this->record('visitConstDeclExpr', $expr);
        }
        public function visitDeclExpr($expr)
        {
            $this->record('visitDeclExpr', $expr);
        }
        public function visitExprStatement($expr)
        {
            $this->record('visitExprStatement', $expr);
        }
        public function visitFloatLiteral($expr)
        {
            $this->record('visitFloatLiteral', $expr);
        }
        public function visitForStatement($stmt)
        {
            $this->record('visitForStatement', $stmt);
        }
        public function visitIdentifier($expr)
        {
            $this->record('visitIdentifier', $expr);
        }
        public function visitIfStatement($stmt)
        {
            $this->record('visitIfStatement', $stmt);
        }
        public function visitIntLiteral($expr)
        {
            $this->record('visitIntLiteral', $expr);
        }
        public function visitLiteral($expr)
        {
            $this->record('visitLiteral', $expr);
        }
        public function visitNullLiteral($expr)
        {
            $this->record('visitNullLiteral', $expr);
        }
        public function visitPostfixExpr($expr)
        {
            $this->record('visitPostfixExpr', $expr);
        }
        public function visitProgram($program)
        {
            $this->record('visitProgram', $program);
        }
        public function visitStringLiteral($expr)
        {
            $this->record('visitStringLiteral', $expr);
        }
        public function visitUnaryExpr($expr)
        {
            $this->record('visitUnaryExpr', $expr);
        }
        public function visitVarDeclExpr($expr)
        {
            $this->record('visitVarDeclExpr', $expr);
        }
    };
}

dataset('ast nodes', function () {
    $identifier = new Identifier(makeAstToken());
    $literalToken = makeAstToken(TokenType::INT, 1);

    return [
        'AssignExpr' => [
            fn() => new AssignExpr(makeAstToken(), $identifier),
            'visitAssignExpr',
        ],
        'BinaryExpr' => [
            fn() => new BinaryExpr($identifier, makeAstToken(TokenType::PLUS, '+'), $identifier),
            'visitBinaryExpr',
        ],
        'Block' => [
            fn() => new Block([]),
            'visitBlock',
        ],
        'BoolLiteral' => [
            fn() => new BoolLiteral(makeAstToken(TokenType::BOOL, 'vero')),
            'visitBoolLiteral',
        ],
        'CallExpr' => [
            fn() => new CallExpr(makeAstToken(), []),
            'visitCallExpr',
        ],
        'ConstDeclExpr' => [
            fn() => new ConstDeclExpr(makeAstToken(), $identifier),
            'visitConstDeclExpr',
        ],
        'ExprStatement' => [
            fn() => new ExprStatement($identifier),
            'visitExprStatement',
        ],
        'FloatLiteral' => [
            fn() => new FloatLiteral(makeAstToken(TokenType::FLOAT, 1.5)),
            'visitFloatLiteral',
        ],
        'ForStatement' => [
            fn() => new ForStatement(null, null, null, new Block([])),
            'visitForStatement',
        ],
        'Identifier' => [
            fn() => new Identifier(makeAstToken()),
            'visitIdentifier',
        ],
        'IfStatement' => [
            fn() => new IfStatement($identifier, new Block([])),
            'visitIfStatement',
        ],
        'IntLiteral' => [
            fn() => new IntLiteral($literalToken),
            'visitIntLiteral',
        ],
        'NullLiteral' => [
            fn() => new NullLiteral(makeAstToken(TokenType::NULL, 'nullo')),
            'visitNullLiteral',
        ],
        'PostfixExpr' => [
            fn() => new PostfixExpr($identifier, makeAstToken(TokenType::INCREMENT, '++')),
            'visitPostfixExpr',
        ],
        'StringLiteral' => [
            fn() => new StringLiteral(makeAstToken(TokenType::STRING, 'ciao')),
            'visitStringLiteral',
        ],
        'UnaryExpr' => [
            fn() => new UnaryExpr(makeAstToken(TokenType::MINUS, '-'), $identifier),
            'visitUnaryExpr',
        ],
        'VarDeclExpr' => [
            fn() => new VarDeclExpr(makeAstToken(), $identifier),
            'visitVarDeclExpr',
        ],
    ];
});

it('dispatches accept() to the matching visitor method', function (callable $makeNode, string $method) {
    $node = $makeNode();
    $visitor = makeAstVisitorSpy();

    $node->accept($visitor);

    expect($visitor->calls)->toBe([[$method, $node]]);
})->with('ast nodes');

it('dispatches Program::accept() to visitProgram', function () {
    $program = new Program([]);
    $visitor = makeAstVisitorSpy();

    $program->accept($visitor);

    expect($visitor->calls)->toBe([['visitProgram', $program]]);
});
