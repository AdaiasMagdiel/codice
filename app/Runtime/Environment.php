<?php

namespace App\Runtime;

use App\Exceptions\RuntimeError;
use App\Lexer\Token;
use App\Types\Chiamabile;

class Environment
{
    public array $globals = [];
    public array $constants = [];

    public function __construct(public ?Environment $enclosing = null)
    {
        if ($enclosing === null) {
            $this->globals["stampa"] = new Chiamabile('stampa', function (...$args) {
                echo implode("", $args) . PHP_EOL;
            });
        }
    }

    public function has(Token $identifier): bool
    {
        return array_key_exists($identifier->lexeme, $this->globals);
    }

    public function get(Token $identifier)
    {
        if ($this->has($identifier)) {
            return $this->globals[$identifier->lexeme];
        }

        if ($this->enclosing !== null) {
            return $this->enclosing->get($identifier);
        }

        throw new RuntimeError(
            "Identificatore '{$identifier->lexeme}' non definito.",
            $identifier->loc
        );
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
        if ($this->has($identifier)) {
            if (isset($this->constants[$identifier->lexeme])) {
                throw new RuntimeError(
                    "Impossibile riassegnare la costante '{$identifier->lexeme}'.",
                    $identifier->loc
                );
            }

            $this->globals[$identifier->lexeme] = $value;
            return;
        }

        if ($this->enclosing !== null) {
            $this->enclosing->assign($identifier, $value);
            return;
        }

        throw new RuntimeError(
            "Identificatore '{$identifier->lexeme}' non definito.",
            $identifier->loc
        );
    }
}
