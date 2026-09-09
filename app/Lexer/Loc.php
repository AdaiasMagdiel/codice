<?php

namespace App\Lexer;

class Loc
{
	public function __construct(
		public string $file = "stdin",
		public int $line = 1,
		public int $col = 1,
		public array $lines = [],
		public int $length = 1
	) {}

	public function __toString()
	{
		return "{$this->file}:{$this->line}:{$this->col}";
	}
}
