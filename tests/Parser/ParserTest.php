<?php

use App\Ast\BinaryExpr;
use App\Ast\CallExpr;
use App\Ast\ExprStatement;
use App\Ast\FloatLiteral;
use App\Ast\Identifier;
use App\Ast\IntLiteral;
use App\Ast\Program;
use App\Ast\StringLiteral;
use App\Ast\UnaryExpr;
use App\Enums\TokenType;
use App\Exceptions\ParseError;
use App\Lexer\Scanner;
use App\Parser\Parser;

function parseParserSource(string $source): Program
{
    $scanner = new Scanner();
    $scanner->init('test.cod', $source);

    $parser = new Parser();
    $parser->init($scanner->scan());

    return $parser->parse();
}

it('parses an empty program', function () {
    $program = parseParserSource('');

    expect($program->statements)->toBe([]);
});

it('parses a string literal expression statement', function () {
    $program = parseParserSource('"ciao";');

    expect($program->statements)->toHaveCount(1)
        ->and($program->statements[0])->toBeInstanceOf(ExprStatement::class)
        ->and($program->statements[0]->expr)->toBeInstanceOf(StringLiteral::class)
        ->and($program->statements[0]->expr->token->lexeme)->toBe('ciao');
});

it('keeps unknown letter escapes in string literals', function () {
    $program = parseParserSource('"ciao\z";');

    expect($program->statements[0]->expr)->toBeInstanceOf(StringLiteral::class)
        ->and($program->statements[0]->expr->token->lexeme)->toBe('ciao\z');
});

it('parses an identifier expression statement', function () {
    $program = parseParserSource('saluto;');

    expect($program->statements)->toHaveCount(1)
        ->and($program->statements[0]->expr)->toBeInstanceOf(Identifier::class)
        ->and($program->statements[0]->expr->token->lexeme)->toBe('saluto');
});

it('parses an integer literal expression statement', function () {
    $program = parseParserSource('42;');

    expect($program->statements[0]->expr)->toBeInstanceOf(IntLiteral::class)
        ->and($program->statements[0]->expr->token->lexeme)->toBe(42);
});

it('parses a float literal expression statement', function () {
    $program = parseParserSource('3.14;');

    expect($program->statements[0]->expr)->toBeInstanceOf(FloatLiteral::class)
        ->and($program->statements[0]->expr->token->lexeme)->toBe(3.14);
});

it('parses a unary plus expression', function () {
    $program = parseParserSource('+42;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(UnaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::PLUS)
        ->and($expr->right)->toBeInstanceOf(IntLiteral::class);
});

it('parses a unary minus expression', function () {
    $program = parseParserSource('-42;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(UnaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::MINUS)
        ->and($expr->right)->toBeInstanceOf(IntLiteral::class);
});

it('parses a binary addition expression', function () {
    $program = parseParserSource('1 + 2;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->left)->toBeInstanceOf(IntLiteral::class)
        ->and($expr->op->type)->toBe(TokenType::PLUS)
        ->and($expr->right)->toBeInstanceOf(IntLiteral::class);
});

it('parses a binary subtraction expression', function () {
    $program = parseParserSource('5 - 2;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::MINUS);
});

it('parses a binary multiplication expression', function () {
    $program = parseParserSource('5 * 2;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::STAR);
});

it('parses a binary division expression', function () {
    $program = parseParserSource('5 / 2;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::SLASH);
});

it('gives multiplication higher precedence than addition', function () {
    $program = parseParserSource('2 + 3 * 4;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::PLUS)
        ->and($expr->left)->toBeInstanceOf(IntLiteral::class)
        ->and($expr->right)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->right->op->type)->toBe(TokenType::STAR);
});

it('lets parentheses override operator precedence', function () {
    $program = parseParserSource('(2 + 3) * 4;');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::STAR)
        ->and($expr->left)->toBeInstanceOf(BinaryExpr::class)
        ->and($expr->left->op->type)->toBe(TokenType::PLUS)
        ->and($expr->right)->toBeInstanceOf(IntLiteral::class);
});

it('parses nested unary expressions', function () {
    $program = parseParserSource('-(-7);');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(UnaryExpr::class)
        ->and($expr->op->type)->toBe(TokenType::MINUS)
        ->and($expr->right)->toBeInstanceOf(UnaryExpr::class)
        ->and($expr->right->op->type)->toBe(TokenType::MINUS)
        ->and($expr->right->right)->toBeInstanceOf(IntLiteral::class);
});

it('parses a call expression without arguments', function () {
    $program = parseParserSource('saluta();');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(CallExpr::class)
        ->and($expr->callee)->toBe('saluta')
        ->and($expr->args)->toBe([]);
});

it('parses a call expression with arguments', function () {
    $program = parseParserSource('saluta("ciao", nome);');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(CallExpr::class)
        ->and($expr->callee)->toBe('saluta')
        ->and($expr->args)->toHaveCount(2)
        ->and($expr->args[0])->toBeInstanceOf(StringLiteral::class)
        ->and($expr->args[0]->token->lexeme)->toBe('ciao')
        ->and($expr->args[1])->toBeInstanceOf(Identifier::class);
});

it('parses nested call expressions as arguments', function () {
    $program = parseParserSource('scrivi(formatta("ciao"));');
    $expr = $program->statements[0]->expr;

    expect($expr)->toBeInstanceOf(CallExpr::class)
        ->and($expr->callee)->toBe('scrivi')
        ->and($expr->args)->toHaveCount(1)
        ->and($expr->args[0])->toBeInstanceOf(CallExpr::class)
        ->and($expr->args[0]->callee)->toBe('formatta');
});

it('reports a parse error when a semicolon is missing', function () {
    parseParserSource('saluta()');
})->throws(ParseError::class, "Atteso 'SEMICOLON', ma è stato trovato 'EOF'.");

it('reports a parse error when an expression is missing', function () {
    parseParserSource(';');
})->throws(ParseError::class, 'Atteso un valore.');

it('parses multiple statements in a single program', function () {
    $program = parseParserSource('saluto; saluta();');

    expect($program->statements)->toHaveCount(2)
        ->and($program->statements[0]->expr)->toBeInstanceOf(Identifier::class)
        ->and($program->statements[1]->expr)->toBeInstanceOf(CallExpr::class);
});

it('reports a parse error when an empty call is missing the closing parenthesis', function () {
    parseParserSource('saluta(');
})->throws(ParseError::class, 'Atteso un valore.');

it('reports a parse error when a call with arguments is missing the closing parenthesis', function () {
    parseParserSource('saluta("ciao"');
})->throws(ParseError::class, "Atteso 'RIGHT_PAREN', ma è stato trovato 'EOF'.");

it('reports a parse error on a trailing comma in an argument list', function () {
    parseParserSource('saluta("ciao",);');
})->throws(ParseError::class, 'Atteso un valore.');
