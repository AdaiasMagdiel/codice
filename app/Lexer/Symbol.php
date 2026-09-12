<?php

namespace App\Lexer;

use App\Enums\TokenType;

class Symbol
{
    private static array $symbols = [
        ';' => TokenType::SEMICOLON,
        '(' => TokenType::LEFT_PAREN,
        ')' => TokenType::RIGHT_PAREN,
        "," => TokenType::COMMA,
        "+" => TokenType::PLUS,
        "-" => TokenType::MINUS,
        "*" => TokenType::STAR,
        "/" => TokenType::SLASH
    ];

    public static function from(string $value): ?TokenType
    {
        return self::$symbols[$value] ?? null;
    }
}
