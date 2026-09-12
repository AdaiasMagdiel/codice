<?php

namespace App\Lexer;

use App\Enums\TokenType;
use App\Exceptions\CodiceError;
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

	private function utf8CharLength(string $byte): int
	{
		$ord = ord($byte);
		if ($ord < 0x80) return 1;        	  // 0xxxxxxx
		if (($ord & 0xE0) === 0xC0) return 2; // 110xxxxx
		if (($ord & 0xF0) === 0xE0) return 3; // 1110xxxx
		if (($ord & 0xF8) === 0xF0) return 4; // 11110xxx
		return 1; // invalid/continuation byte, fallback
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
		$index = $this->pos + $offset;

		if ($index < 0 || $index >= $this->length) {
			return "";
		}

		return $this->content[$index];
	}

	private function match(string $value): bool
	{
		if ($this->pos + strlen($value) > $this->length) {
			return false;
		}

		return substr_compare($this->content, $value, $this->pos, strlen($value)) === 0;
	}

	private function currentChar(): string
	{
		$len = $this->utf8CharLength($this->peek());
		return substr($this->content, $this->pos, $len);
	}

	private function consume(int $times = 1): string
	{
		$result = "";

		for ($i = 0; $i < $times; $i++) {
			$ch = $this->currentChar();

			if ($ch === "\n") {
				$this->line++;
				$this->col = 1;
			} else {
				$this->col++;
			}

			$this->pos += strlen($ch);
			$result .= $ch;
		}

		return $result;
	}

	private function extractIdentifier(): string
	{
		$start = $this->pos;

		while (!$this->isAtEnd()) {
			$ch = $this->peek();

			// bytes >= 0x80 belong to a multibyte UTF-8 character (e.g. accented
			// letters); ctype_alnum() only understands ASCII, so it can't classify them.
			if ($ch !== '_' && !ctype_alnum($ch) && ord($ch) < 0x80) {
				break;
			}

			$this->consume();
		}

		return substr($this->content, $start, $this->pos - $start);
	}

	private function extractString(Loc $loc): string
	{
		$start = $this->pos;
		$this->consume();
		$chars = [];

		while (true) {

			if ($this->isAtEnd()) {
				$loc->length = $this->pos - $start;
				throw new LexError(CodiceError::UNTERMINATED_STRING, $loc);
			}

			$ch = $this->peek();

			if ($ch === "\n") {
				$loc->length = $this->pos - $start;
				throw new LexError(CodiceError::UNTERMINATED_STRING, $loc);
			}

			if ($ch === '"') {
				$this->consume();
				break;
			}

			if ($ch === "\\") {
				$this->consume();

				if ($this->isAtEnd()) {
					$loc->length = $this->pos - $start;
					throw new LexError(CodiceError::UNTERMINATED_STRING, $loc);
				}

				$escaped = $this->consume();
				$escape = match ($escaped) {
					'"' => '"',
					"\\" => "\\",
					"n" => "\n",
					"t" => "\t",
					"r" => "\r",
					default => null,
				};

				if ($escape === null) {
					$escape = "\\" . $escaped;
				}

				$chars[] = $escape;
				continue;
			}

			$chars[] = $this->consume();
		}

		$loc->length = $this->pos - $start;

		return implode("", $chars);
	}

	private function extractNumber(Loc $loc): string
	{
		$start = $this->pos;
		$isFloat = false;
		$chars = [];

		while (!$this->isAtEnd()) {
			$ch = $this->peek();

			if ($ch === '_') {
				$this->consume();

				if (!ctype_digit($this->peek())) {
					$loc->length = $this->pos - $start;
					throw new LexError("Atteso un numero dopo '_'.", $loc);
				}

				continue;
			}

			if ($ch === '.') {
				if ($isFloat) break;

				$isFloat = true;
				$chars[] = $this->consume();

				if ($this->peek() === '_') {
					throw new LexError("Atteso un numero dopo il punto decimale, ma trovato '_'.", $loc);
				}

				continue;
			}

			if (!ctype_digit($ch)) break;

			$chars[] = $this->consume();
		}

		$loc->length = $this->pos - $start;

		if (count($chars) > 1 && $chars[0] === '0' && ctype_digit($chars[1])) {
			throw new LexError("Numero non valido: zero iniziale non consentito.", $loc);
		}

		return implode("", $chars);
	}

	private function skipComment(): void
	{
		$loc = $this->getLoc();

		if ($this->match("/*")) {
			$this->consume(2);

			while (true) {
				if ($this->isAtEnd()) {
					throw new LexError(CodiceError::UNTERMINATED_COMMENT, $loc);
				}

				if ($this->match("*/")) {
					$this->consume(2);
					break;
				}

				$this->consume();
			}
		} else if ($this->peek() === '#' || $this->match("//")) {
			while (!$this->isAtEnd()) {
				if ($this->peek() === "\n") break;

				$this->consume();
			}
		}
	}

	public function scan(): array
	{
		$tokens = [];

		while (!$this->isAtEnd()) {
			$ch = $this->peek();

			// whitespaces
			if (in_array($ch, $this->whitespaces)) {
				$this->consume();
			}

			// comments
			else if ($ch === "#" || $this->match("//") || $this->match("/*")) {
				$this->skipComment();
			}

			// symbols
			else if (($tokenType = Symbol::from($ch)) !== null) {
				$tokens[] = new Token($tokenType, $ch, $this->getLoc());
				$this->consume();
			}

			// identifiers and keywords (bytes >= 0x80 are multibyte letters, e.g. accented characters)
			else if ($ch === '_' || ctype_alpha($ch) || ord($ch) >= 0x80) {
				$loc = $this->getLoc();
				$lexeme = $this->extractIdentifier();
				$loc->length = strlen($lexeme);

				$tokenType = Keyword::from($lexeme) ?? TokenType::IDENTIFIER;

				$tokens[] = new Token($tokenType, $lexeme, $loc);
			}

			// strings
			else if ($ch === '"') {
				$loc = $this->getLoc();
				$lexeme = $this->extractString($loc);

				$tokens[] = new Token(TokenType::STRING, $lexeme, $loc);
			}

			// numbers
			else if (($ch === '.' && ctype_digit($this->peek(1))) || ctype_digit($ch)) {
				$loc = $this->getLoc();
				$lexeme = $this->extractNumber($loc);

				$isFloat = str_contains($lexeme, '.');
				$type  = $isFloat ? TokenType::FLOAT : TokenType::INT;
				$value = $isFloat ? (float) $lexeme : (int) $lexeme;

				$tokens[] = new Token($type, $value, $loc);
			}

			// default
			else {
				throw new LexError("Valore inatteso '{$this->currentChar()}'.", $this->getLoc());
			}
		}

		$tokens[] = new Token(TokenType::EOF, null, $this->getLoc());

		return $tokens;
	}
}
