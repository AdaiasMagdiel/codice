<?php

namespace App\Lexer;

use App\Enums\TokenType;

class Keyword
{
	private static array $keywords = [];

	public static function from(string $value): ?TokenType
	{
		return self::$keywords[$value] ?? null;
	}
}
