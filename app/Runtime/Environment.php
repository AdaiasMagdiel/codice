<?php

namespace App\Runtime;

use App\Ast\Identifier;
use App\Exceptions\RuntimeError;
use App\Lexer\Loc;
use App\Lexer\Token;

class Environment
{
    public array $globals = [];
    private array $constants = [];

    public function __construct()
    {
        $this->globals = [
            "stampa" => function (...$args) {
                echo implode("", $args) . PHP_EOL;
            }
        ];
    }

    public function has(Token $identifier): bool
    {
        return array_key_exists($identifier->lexeme, $this->globals);
    }

    public function get(Token $identifier)
    {
        if (!$this->has($identifier)) {
            throw new RuntimeError(
                "Identificatore '{$identifier->lexeme}' non definito.",
                $identifier->loc
            );
        }

        return $this->globals[$identifier->lexeme];
    }

    public function define(Token $identifier, mixed $value)
    {
        if ($this->has($identifier)) {
            throw new RuntimeError(
                "L'identificatore '{$identifier->lexeme}' è già stato dichiarato.",
                $identifier->loc
            );
        }

        $this->globals[$identifier->lexeme] = $value;
    }

    public function defineConst(Token $identifier, mixed $value)
    {
        $this->define($identifier, $value);
        $this->constants[$identifier->lexeme] = true;
    }

    public function assign(Token $identifier, mixed $value)
    {
        if (!$this->has($identifier)) {
            throw new RuntimeError(
                "Identificatore '{$identifier->lexeme}' non definito.",
                $identifier->loc
            );
        }

        if (isset($this->constants[$identifier->lexeme])) {
            throw new RuntimeError(
                "Impossibile riassegnare la costante '{$identifier->lexeme}'.",
                $identifier->loc
            );
        }

        $this->globals[$identifier->lexeme] = $value;
    }
}
