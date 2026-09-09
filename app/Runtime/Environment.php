<?php

namespace App\Runtime;

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
}
