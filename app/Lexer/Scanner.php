<?php

namespace App\Lexer;

use App\Enums\TokenType;
use App\Exceptions\LexError;

class Scanner
{
	private int $pos = 0;
	private int $line = 1;
	private int $col = 1;
	private string $content = "";
	private int $length = 0;
	private string $file;
	private array $lines = [];

	private array $whitespaces = [
		" ",   // Space (ASCII 32)
		"\t",  // Horizontal Tab (ASCII 9)
		"\n",  // Line Feed / Newline (ASCII 10)
		"\r",  // Carriage Return (ASCII 13)
		"\v",  // Vertical Tab (ASCII 11)
		"\f",  // Form Feed (ASCII 12)
	];

	public function init(string $file = "stdin", string $content = "")
	{
		$this->file = $file;
		$this->content = $content;
		$this->length = strlen($content);
		$this->lines = explode("\n", $content);
		$this->reset();
	}

	public function reset(): void
	{
		$this->pos = 0;
		$this->line = 1;
		$this->col = 1;
	}

	private function isAtEnd(): bool
	{
		return $this->pos >= $this->length;
	}

	private function getLoc(): Loc
	{
		return new Loc($this->file, $this->line, $this->col, $this->lines);
	}

	private function peek(int $offset = 0): string
	{
		return $this->content[$this->pos + $offset];
	}

	private function consume(): string
	{
		$ch = $this->peek();

		if ($ch === "\n") {
			$this->line++;
			$this->col = 1;
		} else {
			$this->col++;
		}

		$this->pos++;
		return $ch;
	}

	private function extractIdentifierOrKeyword(): string
	{
		$start = $this->pos;

		while (!$this->isAtEnd()) {
			$ch = $this->peek();

			if ($ch !== '_' && !ctype_alnum($ch)) {
				break;
			}

			$this->consume();
		}

		return substr($this->content, $start, $this->pos - $start);
	}

	private function extractString(): string|false
	{
		$this->consume();
		$start = $this->pos;
		$chars = [];

		while (true) {
			if ($this->isAtEnd()) {
				return false;
			}

			$ch = $this->peek();

			if ($ch === "\n") {
				return false;
			}

			$chars[] = $this->consume();

			if ($ch === '"') {
				break;
			}
		}

		return implode("", $chars);
	}

	public function scan(): array|false
	{
		$tokens = [];

		while (!$this->isAtEnd()) {
			$ch = $this->peek();

			// whitespaces
			if (in_array($ch, $this->whitespaces)) {
				$this->consume();
			}

			// Symbols
			else if (($tokenType = Symbol::from($ch)) !== null) {
				$tokens[] = new Token($tokenType, $ch, $this->getLoc());
				$this->consume();
			}

			// identifiers and keywords
			else if ($ch === '_' || ctype_alpha($ch)) {
				$loc = $this->getLoc();
				$lexeme = $this->extractIdentifierOrKeyword();
				$loc->length = strlen($lexeme);

				$tokenType = Keyword::from($lexeme) ?? TokenType::IDENTIFIER;

				$tokens[] = new Token($tokenType, $lexeme, $loc);
			}

			// Strings
			else if ($ch === '"') {
				$loc = $this->getLoc();
				$lexeme = $this->extractString();

				if ($lexeme === false) {
					throw new LexError("String non terminata", $loc);
				}

				$loc->length = strlen($lexeme) + 2;
				$tokens[] = new Token(TokenType::STRING, $lexeme, $loc);
			}

			// default
			else {
				throw new LexError("Valore inatteso '{$ch}'", $this->getLoc());
			}
		}

		$tokens[] = new Token(TokenType::EOF, null, $this->getLoc());

		return $tokens;
	}
}
