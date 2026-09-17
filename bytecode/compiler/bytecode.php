<?php

use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Visitors\ByteCode;

require_once __DIR__ . "/../../vendor/autoload.php";

$file = $argv[1] ?? __DIR__ . '/programs/hello_world.cod';

$outputFile = __DIR__ . '/../output/' . pathinfo($file, PATHINFO_FILENAME) . '.codc';

$scanner = new Scanner();
$parser = new Parser();
$bytecode = new ByteCode($outputFile);

$scanner->init(basename($file), file_get_contents($file));

$tokens = $scanner->scan();
$parser->init($tokens);

$program = $parser->parse();

$program->accept($bytecode);
