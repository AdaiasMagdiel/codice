<?php

use App\Codice;
use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Visitors\ByteCode;

require_once __DIR__ . "/vendor/autoload.php";

$outputFile = __DIR__ . "/bytecode.codb";

$scanner = new Scanner();
$parser = new Parser();
$bytecode = new ByteCode(new Environment(), $outputFile);

$scanner->init("stdin", '42 + "Olá, Mundo!"; 67 + 42; 1 + 2 + 3; "Olá, Mundo!";');

$tokens = $scanner->scan();
$parser->init($tokens);

$program = $parser->parse();

$program->accept($bytecode);
