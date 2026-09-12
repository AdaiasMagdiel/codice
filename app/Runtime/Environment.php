<?php

namespace App\Runtime;

use App\Ast\Identifier;
use App\Exceptions\RuntimeError;
use App\Lexer\Loc;

class Environment
{
    public array $globals = [];

    public function __construct()
    {
        $this->globals = [
            "stampa" => function (...$args) {
                echo implode("", $args) . PHP_EOL;
            }
        ];
    }

    public function getIdentifier(Identifier $identifier)
    {
        $key = $identifier->token->lexeme;

        if (!array_key_exists($key, $this->globals)) {
            throw new RuntimeError(
                "Identificatore '{$key}' non definito.",
                $identifier->token->loc
            );
        }

        return $this->globals[$identifier->token->lexeme];
    }

    public function getFunction(string $callee, Loc $loc)
    {
        $fn = $this->globals[$callee] ?? null;

        if (!is_callable($fn)) {
            throw new RuntimeError("Atteso che '{$callee}' fosse una funzione.", $loc);
        }

        return $fn;
    }

    public function setIdentifier(Identifier $identifier, mixed $value)
    {
        $key = $identifier->token->lexeme;

        if (array_key_exists($key, $this->globals)) {
            throw new RuntimeError(
                "L'identificatore '{$key}' è già stato dichiarato.",
                $identifier->token->loc
            );
        }

        $this->globals[$key] = $value;
    }
}
