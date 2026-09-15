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
    $name = basename($file);
    $expectedFile = substr($file, 0, -4) . '.out';

    // An example without a matching .out isn't ready yet (e.g. still a
    // work in progress), so we skip registering a test for it entirely.
    if (!file_exists($expectedFile)) {
        continue;
    }

    it("runs {$name} and matches its expected output", function () use ($file, $expectedFile) {
        expect(runExampleFile($file))->toBe(file_get_contents($expectedFile));
    });
}

it('finds at least one example file to run', function () use ($examples) {
    expect($examples)->not->toBeEmpty();
});
