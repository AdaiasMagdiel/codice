<?php

use App\Exceptions\DivisionByZeroError;
use App\Exceptions\RuntimeError;
use App\Exceptions\TypeError;
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

it('evaluates an integer literal', function () {
    expect(runInterpreterSource('stampa(42);'))->toBe("42" . PHP_EOL);
});

it('evaluates an integer literal with underscores as digit separators', function () {
    expect(runInterpreterSource('stampa(1_000_000);'))->toBe("1000000" . PHP_EOL);
});

it('evaluates a float literal', function () {
    expect(runInterpreterSource('stampa(3.1415926535);'))->toBe("3.1415926535" . PHP_EOL);
});

it('evaluates a unary plus on an integer', function () {
    expect(runInterpreterSource('stampa(+42);'))->toBe("42" . PHP_EOL);
});

it('evaluates a unary minus on an integer', function () {
    expect(runInterpreterSource('stampa(-42);'))->toBe("-42" . PHP_EOL);
});

it('evaluates nested unary minus expressions', function () {
    expect(runInterpreterSource('stampa(-(-7));'))->toBe("7" . PHP_EOL);
});

it('throws a type error when applying unary minus to a non-numeric value', function () {
    runInterpreterSource('stampa(-vero);');
})->throws(TypeError::class, 'Atteso intero o decimale, ma trovato booleano.');

it('evaluates binary addition between integers', function () {
    expect(runInterpreterSource('stampa(1 + 2);'))->toBe("3" . PHP_EOL);
});

it('evaluates binary addition producing a float when either operand is a float', function () {
    expect(runInterpreterSource('stampa(2.5 + 1);'))->toBe("3.5" . PHP_EOL);
});

it('concatenates strings with the plus operator', function () {
    expect(runInterpreterSource('stampa("ciao" + " mondo");'))->toBe("ciao mondo" . PHP_EOL);
});

it('concatenates a string with a number using the plus operator', function () {
    expect(runInterpreterSource('stampa("valore: " + 42);'))->toBe("valore: 42" . PHP_EOL);
});

it('evaluates binary subtraction between integers', function () {
    expect(runInterpreterSource('stampa(10 - 3);'))->toBe("7" . PHP_EOL);
});

it('throws a type error when subtracting a string', function () {
    runInterpreterSource('stampa("a" - 1);');
})->throws(TypeError::class, 'Atteso intero o decimale, ma trovato stringa.');

it('evaluates binary multiplication between integers', function () {
    expect(runInterpreterSource('stampa(4 * 3);'))->toBe("12" . PHP_EOL);
});

it('repeats a string when multiplied by an integer', function () {
    expect(runInterpreterSource('stampa("ab" * 3);'))->toBe("ababab" . PHP_EOL);
});

it('repeats a string when an integer multiplies it from the left', function () {
    expect(runInterpreterSource('stampa(3 * "ab");'))->toBe("ababab" . PHP_EOL);
});

it('throws a type error when multiplying two strings', function () {
    runInterpreterSource('stampa("a" * "b");');
})->throws(TypeError::class, "Operatore '*' non applicabile a due stringhe.");

it('throws a type error when multiplying a string by a float', function () {
    runInterpreterSource('stampa("a" * 2.5);');
})->throws(TypeError::class, "Operatore '*' non applicabile tra stringa e decimale.");

it('throws a type error when multiplying a string by a negative integer', function () {
    runInterpreterSource('stampa("a" * -1);');
})->throws(TypeError::class, 'Il moltiplicatore della stringa non può essere negativo.');

it('evaluates binary division between integers as a float', function () {
    expect(runInterpreterSource('stampa(10 / 4);'))->toBe("2.5" . PHP_EOL);
});

it('evaluates an evenly divisible division as a float with a decimal point', function () {
    expect(runInterpreterSource('stampa(10 / 5);'))->toBe("2.0" . PHP_EOL);
});

it('throws when dividing by zero', function () {
    runInterpreterSource('stampa(10 / 0);');
})->throws(DivisionByZeroError::class, 'Impossibile dividere per zero.');

it('gives multiplication higher precedence than addition', function () {
    expect(runInterpreterSource('stampa(2 + 3 * 4);'))->toBe("14" . PHP_EOL);
});

it('lets parentheses override operator precedence', function () {
    expect(runInterpreterSource('stampa((2 + 3) * 4);'))->toBe("20" . PHP_EOL);
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
})->throws(RuntimeError::class, "Identificatore 'saluta' non definito.");

it('throws when referencing an undefined identifier', function () {
    runInterpreterSource('sconosciuto;');
})->throws(RuntimeError::class, "Identificatore 'sconosciuto' non definito.");

it('declares a variable with an initial value', function () {
    expect(runInterpreterSource('sia x = 5; stampa(x);'))->toBe("5" . PHP_EOL);
});

it('defaults a variable declared without an initial value to nullo', function () {
    expect(runInterpreterSource('sia x; stampa(x);'))->toBe("nullo" . PHP_EOL);
});

it('evaluates a variable declaration to the assigned value', function () {
    expect(runInterpreterSource('stampa(sia x = 5);'))->toBe("5" . PHP_EOL);
});

it('evaluates a variable declaration used inside a larger expression', function () {
    expect(runInterpreterSource('stampa((sia x = 5) + 1);'))->toBe("6" . PHP_EOL);
});

it('keeps a variable declared inside a parenthesized expression available afterwards', function () {
    expect(runInterpreterSource('sia y = (sia x = 5) * 2; stampa(x, " ", y);'))->toBe("5 10" . PHP_EOL);
});

it('throws when declaring a variable that is already declared', function () {
    runInterpreterSource('sia x = 1; sia x = 2;');
})->throws(RuntimeError::class, "L'identificatore 'x' è già stato dichiarato.");

it('throws when the outer declaration of a self-nested "sia x = sia x = ..." redeclares x', function () {
    // The inner "sia x = 5" runs first and declares x; the outer declaration
    // then tries to declare the same name again and fails.
    runInterpreterSource('sia x = sia x = 5;');
})->throws(RuntimeError::class, "L'identificatore 'x' è già stato dichiarato.");

it('throws when a variable declaration reuses the name of a builtin function', function () {
    // Variables and builtin functions share the same namespace in Environment,
    // so declaring "stampa" as a variable collides with the builtin.
    runInterpreterSource('sia stampa = 5;');
})->throws(RuntimeError::class, "L'identificatore 'stampa' è già stato dichiarato.");

it('propagates an error raised while evaluating a variable declaration\'s initializer', function () {
    runInterpreterSource('sia x = 1 / 0;');
})->throws(DivisionByZeroError::class, 'Impossibile dividere per zero.');

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
