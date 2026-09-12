<?php

namespace App\Runtime;

use App\Ast\Identifier;
use App\Exceptions\RuntimeError;
use App\Lexer\Loc;
use App\Lexer\Token;

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

    public function get(Token $identifier)
    {
        $key = $identifier->lexeme;

        if (!array_key_exists($key, $this->globals)) {
            throw new RuntimeError(
                "Identificatore '{$key}' non definito.",
                $identifier->loc
            );
        }

        return $this->globals[$key];
    }

    public function define(Token $identifier, mixed $value)
    {
        $key = $identifier->lexeme;

        if (array_key_exists($key, $this->globals)) {
            throw new RuntimeError(
                "L'identificatore '{$key}' è già stato dichiarato.",
                $identifier->loc
            );
        }

        $this->globals[$key] = $value;
    }

    public function assign(Token $identifier, mixed $value)
    {
        $key = $identifier->lexeme;

        if (!array_key_exists($key, $this->globals)) {
            throw new RuntimeError(
                "Identificatore '{$key}' non definito.",
                $identifier->loc
            );
        }

        $this->globals[$key] = $value;
    }
}
