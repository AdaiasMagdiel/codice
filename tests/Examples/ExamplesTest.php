<?php

use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Runtime\Interpreter;

function runExampleFile(string $path): string
{
    $scanner = new Scanner();
    $scanner->init(basename($path), file_get_contents($path));

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();
    $environment = new Environment();

    ob_start();
    try {
        $interpreter->run($program, $environment);
        return ob_get_clean();
    } catch (\Throwable $e) {
        ob_end_clean();
        throw $e;
    }
}

$examples = glob(__DIR__ . '/../../examples/*.cod');

foreach ($examples as $file) {
    it('runs ' . basename($file) . ' without errors', function () use ($file) {
        expect(runExampleFile($file))->not->toBe('');
    });
}

it('finds at least one example file to run', function () use ($examples) {
    expect($examples)->not->toBeEmpty();
});
