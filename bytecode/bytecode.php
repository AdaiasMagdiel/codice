<?php

use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Visitors\ByteCode;

require_once __DIR__ . "/../vendor/autoload.php";

$outputFile = __DIR__ . "/output/program.codb";

if ($argc === 1) {
	$scanner = new Scanner();
	$parser = new Parser();
	$bytecode = new ByteCode(new Environment(), $outputFile);

	$file = __DIR__ . '/programs/hello_world.cod';
	$scanner->init(basename($file), file_get_contents($file));

	$tokens = $scanner->scan();
	$parser->init($tokens);

	$program = $parser->parse();

	$program->accept($bytecode);
	exit(0);
}

echo "---";
