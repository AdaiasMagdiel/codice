<?php

namespace App\Lexer;

use App\Enums\TokenType;

class Symbol
{
    private static array $symbols = [
        ';' => TokenType::SEMICOLON,
        '(' => TokenType::LEFT_PAREN,
        ')' => TokenType::RIGHT_PAREN,
    ];

    public static function from(string $value): ?TokenType
    {
        return self::$symbols[$value] ?? null;
    }
}
