<?php

use App\Exceptions\RuntimeError;
use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Runtime\Interpreter;

function runInterpreterSource(string $source): string
{
    $scanner = new Scanner();
    $scanner->init('test.cod', $source);

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

it('runs an empty program without output', function () {
    expect(runInterpreterSource(''))->toBe('');
});

it('calls a builtin function with a string literal argument', function () {
    expect(runInterpreterSource('stampa("ciao");'))->toBe("ciao" . PHP_EOL);
});

it('calls a builtin function with multiple arguments concatenated', function () {
    expect(runInterpreterSource('stampa("ciao", " ", "mondo");'))->toBe("ciao mondo" . PHP_EOL);
});

it('calls a builtin function with a nested call expression as argument', function () {
    expect(runInterpreterSource('stampa(stampa("ciao"));'))->toBe("ciao" . PHP_EOL . "nullo" . PHP_EOL);
});

it('evaluates the true boolean literal', function () {
    expect(runInterpreterSource('stampa(vero);'))->toBe("vero" . PHP_EOL);
});

it('evaluates the false boolean literal', function () {
    expect(runInterpreterSource('stampa(falso);'))->toBe("falso" . PHP_EOL);
});

it('evaluates the null literal', function () {
    expect(runInterpreterSource('stampa(nullo);'))->toBe("nullo" . PHP_EOL);
});

it('prints multiple literal types together', function () {
    expect(runInterpreterSource('stampa(vero, " ", falso, " ", nullo);'))
        ->toBe("vero falso nullo" . PHP_EOL);
});

it('defaults a function call with no explicit return value to nullo', function () {
    expect(runInterpreterSource('stampa(stampa());'))->toBe(PHP_EOL . "nullo" . PHP_EOL);
});

it('runs multiple statements in order', function () {
    expect(runInterpreterSource('stampa("uno"); stampa("due");'))
        ->toBe("uno" . PHP_EOL . "due" . PHP_EOL);
});

it('evaluates a string literal expression statement without touching the environment', function () {
    expect(runInterpreterSource('"ciao";'))->toBe('');
});

it('resolves an identifier bound in the environment', function () {
    $scanner = new Scanner();
    $scanner->init('test.cod', 'stampa;');

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();
    $environment = new Environment();

    expect($interpreter->run($program, $environment))->toBeNull();
});

it('throws when calling an undefined function', function () {
    runInterpreterSource('saluta();');
})->throws(RuntimeError::class, "Atteso che 'saluta' fosse una funzione.");

it('throws when referencing an undefined identifier', function () {
    runInterpreterSource('sconosciuto;');
})->throws(RuntimeError::class, "Identificatore 'sconosciuto' non definito.");

it('throws when calling something that is not a function', function () {
    $environment = new Environment();
    $environment->globals['naoFuncao'] = 'valor';

    $scanner = new Scanner();
    $scanner->init('test.cod', 'naoFuncao();');

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(RuntimeError::class, "Atteso che 'naoFuncao' fosse una funzione.");
});
