<?php

namespace App\Lexer;

use App\Enums\TokenType;

class Symbol
{
    private static array $symbols = [
        ';'  => TokenType::SEMICOLON,
        '('  => TokenType::LEFT_PAREN,
        ')'  => TokenType::RIGHT_PAREN,
        '{'  => TokenType::LEFT_BRACE,
        '}'  => TokenType::RIGHT_BRACE,
        ","  => TokenType::COMMA,
        "+"  => TokenType::PLUS,
        "-"  => TokenType::MINUS,
        "*"  => TokenType::STAR,
        "/"  => TokenType::SLASH,
        "%"  => TokenType::MOD,
        "="  => TokenType::ASSIGN,
        "++" => TokenType::INCREMENT,
        "--" => TokenType::DECREMENT,
        ">"  => TokenType::GREATER,
        "<"  => TokenType::LESS,
        ">=" => TokenType::GREATER_EQUAL,
        "<=" => TokenType::LESS_EQUAL,
        "==" => TokenType::EQUAL,
        "!=" => TokenType::NOT_EQUAL,
        "&&" => TokenType::AND,
        "||" => TokenType::OR
    ];

    public static function from(string $value): ?TokenType
    {
        return self::$symbols[$value] ?? null;
    }
}
