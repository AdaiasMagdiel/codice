<?php

namespace App\Lexer;

use App\Enums\TokenType;

class Token
{
	public function __construct(
		public TokenType $type,
		public mixed $lexeme,
		public Loc $loc
	) {}
}
