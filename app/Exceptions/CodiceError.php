<?php

namespace App\Exceptions;

use App\Lexer\Loc;
use Exception;
use Override;

class CodiceError extends Exception
{
    public Loc $loc;

    public function __construct(string $message, Loc $loc)
    {
        $this->loc = $loc;
        return parent::__construct($message);
    }

    #[Override]
    public function __toString(): string
    {
        $msg = "Errore: {$this->getMessage()}\n";

        $lineInfo = $this->loc->file . ":" . $this->loc->line . ":" . $this->loc->col . " | ";
        $lineInfoLen = strlen($lineInfo);

        $line = $this->loc->lines[$this->loc->line - 1];
        $tildeCount = max(0, min($this->loc->length, strlen($line) - $this->loc->col + 1) - 1);
        $indicator = str_repeat(" ", $lineInfoLen + $this->loc->col - 1) . "^" . str_repeat("~", $tildeCount);

        return $msg . "\n" . $lineInfo . $line . "\n" . $indicator . "\n";
    }
}
