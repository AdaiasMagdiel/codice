<?php

namespace App\Lexer;

use App\Enums\TokenType;

class Keyword
{
	private static array $keywords = [
		"nullo" => TokenType::NULL,
		"vero"  => TokenType::BOOL,
		"falso" => TokenType::BOOL,
		"sia"	=> TokenType::SIA,
		"cost"	=> TokenType::COST
	];

	public static function from(string $value): ?TokenType
	{
		return self::$keywords[$value] ?? null;
	}
}
