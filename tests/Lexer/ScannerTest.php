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

it('skips a hash line comment up to the newline', function () {
    $tokens = scanLexerSource("# comment\nsaluto");

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto');
});

it('skips a slash-slash line comment up to the newline', function () {
    $tokens = scanLexerSource("// comment\nsaluto");

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto');
});

it('treats a line comment reaching EOF without a trailing newline as valid', function () {
    $tokens = scanLexerSource("saluto // comment");

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[1]->type)->toBe(TokenType::EOF);
});

it('skips a block comment', function () {
    $tokens = scanLexerSource('/* comment */ saluto');

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto');
});

it('skips a block comment spanning multiple lines', function () {
    $tokens = scanLexerSource("/* line 1\nline 2 */ saluto");

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->loc->line)->toBe(2);
});

it('skips an empty block comment', function () {
    $tokens = scanLexerSource('/**/saluto');

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto');
});

it('does not let the opening "/*" double as the closing "*/"', function () {
    // A naive scanner could let the '*' that opens the comment also serve as
    // the '*' that closes it, wrongly treating "/*/" as a terminated comment.
    scanLexerSource('/*/');
})->throws(LexError::class, 'Commento non terminato');

it('throws when a block comment is not terminated before EOF', function () {
    scanLexerSource('/* comment');
})->throws(LexError::class, 'Commento non terminato');

it('treats one comment right after another as two separate comments', function () {
    $tokens = scanLexerSource("/* a */// b\nsaluto");

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto');
});

it('allows a block comment between two other tokens', function () {
    $tokens = scanLexerSource('stampa /* comment */ (saluto)');

    expect($tokens)->toHaveCount(5)
        ->and($tokens[0]->lexeme)->toBe('stampa')
        ->and($tokens[1]->type)->toBe(TokenType::LEFT_PAREN)
        ->and($tokens[2]->lexeme)->toBe('saluto')
        ->and($tokens[3]->type)->toBe(TokenType::RIGHT_PAREN);
});

it('scans an identifier', function () {
    $tokens = scanLexerSource('saluto_1');

    expect($tokens)->toHaveCount(2)
        ->and($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('saluto_1')
        ->and($tokens[0]->loc->length)->toBe(8);
});

it('scans an identifier with multibyte UTF-8 characters', function () {
    $tokens = scanLexerSource('condição');

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('condição')
        ->and($tokens[0]->loc->length)->toBe(strlen('condição'));
});

it('scans an identifier starting with a multibyte UTF-8 character', function () {
    $tokens = scanLexerSource('área');

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('área');
});

it('tracks columns by character, not by byte, across multibyte identifiers', function () {
    $tokens = scanLexerSource('condição x');

    expect($tokens[1]->lexeme)->toBe('x')
        ->and($tokens[1]->loc->col)->toBe(10);
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

it('scans a string literal containing multibyte UTF-8 characters', function () {
    $tokens = scanLexerSource('"È vero, così è!"');

    expect($tokens[0]->lexeme)->toBe('È vero, così è!');
});

it('throws when a string is not terminated before EOF', function () {
    scanLexerSource('"ciao');
})->throws(LexError::class, 'Stringa non terminata');

it('throws when a string is not terminated before a newline', function () {
    scanLexerSource("\"ciao\n\"");
})->throws(LexError::class, 'Stringa non terminata');

it('throws when a string ends right after a trailing backslash', function () {
    scanLexerSource('"ciao\\');
})->throws(LexError::class, 'Stringa non terminata');

it('throws on an unexpected character', function () {
    scanLexerSource('@');
})->throws(LexError::class, "Valore inatteso '@'");

it('treats any non-ASCII byte as part of an identifier, even non-letters like symbols', function () {
    // The lexer has no full Unicode letter table, so it can't tell an accented
    // letter apart from a symbol like '€' based on byte value alone: any byte
    // >= 0x80 is accepted as an identifier character.
    $tokens = scanLexerSource('€');

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('€');
});

it('scans a 4-byte UTF-8 character, such as an emoji', function () {
    $tokens = scanLexerSource('😀');

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe('😀');
});

it('falls back to a single byte for an invalid/standalone UTF-8 byte', function () {
    $tokens = scanLexerSource("\xFF");

    expect($tokens[0]->type)->toBe(TokenType::IDENTIFIER)
        ->and($tokens[0]->lexeme)->toBe("\xFF");
});

it('scans an integer literal', function () {
    $tokens = scanLexerSource('42');

    expect($tokens[0]->type)->toBe(TokenType::INT)
        ->and($tokens[0]->lexeme)->toBe(42)
        ->and($tokens[0]->loc->length)->toBe(2);
});

it('scans an integer literal with underscores as digit separators', function () {
    $tokens = scanLexerSource('1_000_000');

    expect($tokens[0]->type)->toBe(TokenType::INT)
        ->and($tokens[0]->lexeme)->toBe(1000000)
        ->and($tokens[0]->loc->length)->toBe(9);
});

it('scans a float literal', function () {
    $tokens = scanLexerSource('3.1415926535');

    expect($tokens[0]->type)->toBe(TokenType::FLOAT)
        ->and($tokens[0]->lexeme)->toBe(3.1415926535)
        ->and($tokens[0]->loc->length)->toBe(12);
});

it('scans a float literal starting with a leading dot', function () {
    $tokens = scanLexerSource('.5');

    expect($tokens[0]->type)->toBe(TokenType::FLOAT)
        ->and($tokens[0]->lexeme)->toBe(0.5);
});

it('scans a float literal with underscores in the fractional part', function () {
    $tokens = scanLexerSource('1.5_5');

    expect($tokens[0]->type)->toBe(TokenType::FLOAT)
        ->and($tokens[0]->lexeme)->toBe(1.55);
});

it('throws when a number has two consecutive underscores', function () {
    scanLexerSource('1__2');
})->throws(LexError::class, "Atteso un numero dopo '_'.");

it('throws when a number ends with a trailing underscore', function () {
    scanLexerSource('12_');
})->throws(LexError::class, "Atteso un numero dopo '_'.");

it('throws when an underscore follows the decimal point directly', function () {
    scanLexerSource('1._5');
})->throws(LexError::class, "Atteso un numero dopo il punto decimale, ma trovato '_'.");

it('throws on a number with a leading zero', function () {
    scanLexerSource('012');
})->throws(LexError::class, 'Numero non valido: zero iniziale non consentito.');

it('does not treat a lone zero as a leading zero', function () {
    $tokens = scanLexerSource('0');

    expect($tokens[0]->type)->toBe(TokenType::INT)
        ->and($tokens[0]->lexeme)->toBe(0);
});

it('throws when a leading zero is followed by an underscore and a digit', function () {
    scanLexerSource('0_1');
})->throws(LexError::class, 'Numero non valido: zero iniziale non consentito.');

it('tracks line and column across newlines', function () {
    $tokens = scanLexerSource("\n saluto");

    expect($tokens[0]->loc->line)->toBe(2)
        ->and($tokens[0]->loc->col)->toBe(2);
});

it('formats a location as file:line:col', function () {
    $tokens = scanLexerSource(" saluto");

    expect((string) $tokens[0]->loc)->toBe('test.cod:1:2');
});
