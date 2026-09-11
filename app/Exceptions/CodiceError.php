<?php

namespace App\Exceptions;

use App\Lexer\Loc;
use Exception;
use Override;

class CodiceError extends Exception
{
    private const string GRAY = "\033[90m";
    private const string RED = "\033[31m";
    private const string RESET = "\033[0m";

    public const string UNTERMINATED_STRING = "Stringa non terminata.";

    public Loc $loc;

    public function __construct(string $message, Loc $loc)
    {
        $this->loc = $loc;
        return parent::__construct($message);
    }

    #[Override]
    public function __toString(): string
    {
        $lines = $this->loc->lines;
        $lineIdx = $this->loc->line - 1;

        $hasPrev = $lineIdx - 1 >= 0 && isset($lines[$lineIdx - 1]);
        $hasNext = isset($lines[$lineIdx + 1]);

        $useColor = $this->supportsColor();
        $msg = $this->colorize("Errore:", self::RED, $useColor) . " {$this->getMessage()}\n\n";

        $prevPrefix = $hasPrev ? $this->getLinePrefix($this->loc->line - 1) : "";
        $currentPrefix = $this->getLinePrefix($this->loc->line, $this->loc->col);
        $nextPrefix = $hasNext ? $this->getLinePrefix($this->loc->line + 1) : "";

        $prefixLen = max(strlen($prevPrefix), strlen($currentPrefix), strlen($nextPrefix));

        $context = "";

        if ($hasPrev) {
            $row = str_pad($prevPrefix, $prefixLen) . " | " . $lines[$lineIdx - 1];
            $context .= $this->colorize($row, self::GRAY, $useColor) . "\n";
        }

        $line = $lines[$lineIdx];
        $tildeCount = max(0, min($this->loc->length, strlen($line) - $this->loc->col + 1) - 1);
        $indicator = str_repeat(" ", $prefixLen + 3 + $this->loc->col - 1) . "^" . str_repeat("~", $tildeCount);

        $currentRow = $this->colorize(str_pad($currentPrefix, $prefixLen), self::RED, $useColor) . " | " . $line;

        $context .= $currentRow . "\n" . $this->colorize($indicator, self::RED, $useColor) . "\n";

        if ($hasNext) {
            $row = str_pad($nextPrefix, $prefixLen) . " | " . $lines[$lineIdx + 1];
            $context .= $this->colorize($row, self::GRAY, $useColor) . "\n";
        }

        return $msg . $context;
    }

    private function getLinePrefix(int $line, ?int $col = null): string
    {
        if ($col === null) {
            return $this->loc->file . ":" . $line;
        }

        return $this->loc->file . ":" . $line . ":" . $col;
    }

    private function colorize(string $text, string $color, bool $useColor): string
    {
        if (!$useColor) {
            return $text;
        }

        return $color . $text . self::RESET;
    }

    private function supportsColor(): bool
    {
        if (getenv("CODICE_NO_COLOR") !== false) {
            return false;
        }

        if (!defined("STDOUT")) {
            return false;
        }

        if (function_exists("sapi_windows_vt100_support") && sapi_windows_vt100_support(STDOUT)) {
            return true;
        }

        if (function_exists("stream_isatty")) {
            return stream_isatty(STDOUT);
        }

        return false;
    }
}
