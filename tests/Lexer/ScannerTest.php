<?php

use App\Enums\TokenType;
use App\Exceptions\LexError;
use App\Lexer\Scanner;

function scanLexerSource(string $source): array
{
    $scanner = new Scanner();
    $scanner->init('test.cod', $source);

    return $scanner->scan();
}

it('scans an empty source to just EOF', function () {
    $tokens = scanLexerSource('');

    expect($tokens)->toHaveCount(1)
        ->and($tokens[0]->type)->toBe(TokenType::EOF);
});

it('skips whitespace characters', function () {
    $tokens = scanLexerSource(" \t\n\r\v\f;");

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::SEMICOLON);
});

it('scans every symbol token', function () {
    $tokens = scanLexerSource(';(),');

    expect($tokens)->toHaveCount(5)
        ->and($tokens[0]->type)->toBe(TokenType::SEMICOLON)
        ->and($tokens[1]->type)->toBe(TokenType::LEFT_PAREN)
        ->and($tokens[2]->type)->toBe(TokenType::RIGHT_PAREN)
        ->and($tokens[3]->type)->toBe(TokenType::COMMA)
        ->and($tokens[4]->type)->toBe(TokenType::EOF);
});

it('scans an identifier', function () {
    $tokens = scanLexerSource('saluto_1');

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto_1')
        ->and($tokens[0]->loc->length)->toBe(8);
});

it('scans a plain string literal', function () {
    $tokens = scanLexerSource('"ciao"');

    expect($tokens[0]->type)->toBe(TokenType::STRING)
        ->and($tokens[0]->lexeme)->toBe('ciao')
        ->and($tokens[0]->loc->length)->toBe(6);
});

it('resolves known escape sequences in strings', function () {
    $tokens = scanLexerSource('"a\\"b\\\\c\\nd\\te\\rf"');

    expect($tokens[0]->lexeme)->toBe("a\"b\\c\nd\te\rf");
});

it('keeps unknown escape sequences verbatim', function () {
    $tokens = scanLexerSource('"ciao\\z"');

    expect($tokens[0]->lexeme)->toBe('ciao\z');
});

it('throws when a string is not terminated before EOF', function () {
    scanLexerSource('"ciao');
})->throws(LexError::class, 'String non terminata');

it('throws when a string is not terminated before a newline', function () {
    scanLexerSource("\"ciao\n\"");
})->throws(LexError::class, 'String non terminata');

it('throws when a string ends right after a trailing backslash', function () {
    scanLexerSource('"ciao\\');
})->throws(LexError::class, 'String non terminata');

it('throws on an unexpected character', function () {
    scanLexerSource('@');
})->throws(LexError::class, "Valore inatteso '@'");

it('tracks line and column across newlines', function () {
    $tokens = scanLexerSource("\n saluto");

    expect($tokens[0]->loc->line)->toBe(2)
        ->and($tokens[0]->loc->col)->toBe(2);
});

it('formats a location as file:line:col', function () {
    $tokens = scanLexerSource(" saluto");

    expect((string) $tokens[0]->loc)->toBe('test.cod:1:2');
});
