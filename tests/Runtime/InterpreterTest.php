<?php

use App\Ast\BinaryExpr;
use App\Ast\ExprStatement;
use App\Ast\IntLiteral;
use App\Ast\Program;
use App\Ast\UnaryExpr;
use App\Enums\TokenType;
use App\Exceptions\DivisionByZeroError;
use App\Exceptions\RuntimeError;
use App\Exceptions\TypeError;
use App\Interfaces\Expr;
use App\Interfaces\Stmt;
use App\Lexer\Loc;
use App\Lexer\Scanner;
use App\Lexer\Token;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Runtime\Interpreter;
use App\Types\Chiamabile;
use App\Types\Nullo;

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

    expect($interpreter->run($program, $environment))->toBeInstanceOf(Nullo::class);
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

it('declares a constant with its initial value', function () {
    expect(runInterpreterSource('cost PI = 3.14; stampa(PI);'))->toBe("3.14" . PHP_EOL);
});

it('evaluates a constant declaration to the assigned value', function () {
    expect(runInterpreterSource('stampa(cost x = 5);'))->toBe("5" . PHP_EOL);
});

it('throws when declaring a constant that is already declared', function () {
    runInterpreterSource('cost x = 1; cost x = 2;');
})->throws(RuntimeError::class, "L'identificatore 'x' è già stato dichiarato.");

it('throws when a constant declaration reuses the name of an already declared variable', function () {
    runInterpreterSource('sia x = 1; cost x = 2;');
})->throws(RuntimeError::class, "L'identificatore 'x' è già stato dichiarato.");

it('throws when a variable declaration reuses the name of an already declared constant', function () {
    runInterpreterSource('cost x = 1; sia x = 2;');
})->throws(RuntimeError::class, "L'identificatore 'x' è già stato dichiarato.");

it('throws when a constant declaration reuses the name of a builtin function', function () {
    runInterpreterSource('cost stampa = 5;');
})->throws(RuntimeError::class, "L'identificatore 'stampa' è già stato dichiarato.");

it('propagates an error raised while evaluating a constant declaration\'s initializer', function () {
    runInterpreterSource('cost x = 1 / 0;');
})->throws(DivisionByZeroError::class, 'Impossibile dividere per zero.');

it('throws when reassigning a declared constant', function () {
    runInterpreterSource('cost x = 1; x = 2;');
})->throws(RuntimeError::class, "Impossibile riassegnare la costante 'x'.");

it('throws when reassigning a constant inside a chained assignment', function () {
    runInterpreterSource('sia y = 0; cost x = 1; y = x = 2;');
})->throws(RuntimeError::class, "Impossibile riassegnare la costante 'x'.");

it('reassigns a previously declared variable', function () {
    expect(runInterpreterSource('sia x = 1; x = 2; stampa(x);'))->toBe("2" . PHP_EOL);
});

it('evaluates an assignment expression to the assigned value', function () {
    expect(runInterpreterSource('sia x = 1; stampa(x = 5);'))->toBe("5" . PHP_EOL);
});

it('evaluates an assignment expression used inside a larger expression', function () {
    expect(runInterpreterSource('sia x = 1; stampa((x = 5) + 1);'))->toBe("6" . PHP_EOL);
});

it('assigns the same value to every variable in a chained assignment', function () {
    expect(runInterpreterSource('sia x = 0; sia y = 0; x = y = 5; stampa(x, " ", y);'))->toBe("5 5" . PHP_EOL);
});

it('throws when assigning to an identifier that was never declared', function () {
    runInterpreterSource('x = 5;');
})->throws(RuntimeError::class, "Identificatore 'x' non definito.");

it('throws when assigning to an identifier declared only after the assignment', function () {
    runInterpreterSource('x = 5; sia x = 1;');
})->throws(RuntimeError::class, "Identificatore 'x' non definito.");

it('allows reassigning the name of a builtin function, shadowing it', function () {
    expect(fn () => runInterpreterSource('stampa = 5;'))->not->toThrow(RuntimeError::class);
});

it('throws when calling a builtin function after it was shadowed by an assignment', function () {
    runInterpreterSource('stampa = 5; stampa(1);');
})->throws(RuntimeError::class, "Atteso che 'stampa' fosse una funzione.");

it('propagates an error raised while evaluating an assignment\'s value', function () {
    runInterpreterSource('sia x = 1; x = 1 / 0;');
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

it('runs the then branch when the condition is true', function () {
    expect(runInterpreterSource('se (vero) { stampa("sim"); } senon { stampa("nao"); }'))
        ->toBe("sim" . PHP_EOL);
});

it('runs the else branch when the condition is false', function () {
    expect(runInterpreterSource('se (falso) { stampa("sim"); } senon { stampa("nao"); }'))
        ->toBe("nao" . PHP_EOL);
});

it('runs nothing when the condition is false and there is no else branch', function () {
    expect(runInterpreterSource('se (falso) { stampa("sim"); }'))->toBe('');
});

it('chains an else-if to pick the first matching branch', function () {
    $source = 'se (falso) { stampa("um"); } senon se (vero) { stampa("dois"); } senon { stampa("tres"); }';

    expect(runInterpreterSource($source))->toBe("dois" . PHP_EOL);
});

it('falls through an else-if chain to the final else', function () {
    $source = 'se (falso) { stampa("um"); } senon se (falso) { stampa("dois"); } senon { stampa("tres"); }';

    expect(runInterpreterSource($source))->toBe("tres" . PHP_EOL);
});

it('runs a nested if statement inside a block', function () {
    expect(runInterpreterSource('se (vero) { se (vero) { stampa("interno"); } }'))
        ->toBe("interno" . PHP_EOL);
});

it('propagates an error raised while evaluating an if condition', function () {
    runInterpreterSource('se (1 / 0) { stampa("sim"); }');
})->throws(DivisionByZeroError::class, 'Impossibile dividere per zero.');

it('treats a non-zero integer as true', function () {
    expect(runInterpreterSource('se (1) { stampa("sim"); }'))->toBe("sim" . PHP_EOL);
});

it('treats zero as false', function () {
    expect(runInterpreterSource('se (0) { stampa("sim"); } senon { stampa("nao"); }'))
        ->toBe("nao" . PHP_EOL);
});

it('treats a non-zero decimal as true', function () {
    expect(runInterpreterSource('se (1.5) { stampa("sim"); }'))->toBe("sim" . PHP_EOL);
});

it('treats 0.0 as false', function () {
    expect(runInterpreterSource('se (0.0) { stampa("sim"); } senon { stampa("nao"); }'))
        ->toBe("nao" . PHP_EOL);
});

it('treats a non-empty string as true', function () {
    expect(runInterpreterSource('se ("ciao") { stampa("sim"); }'))->toBe("sim" . PHP_EOL);
});

it('treats an empty string as false', function () {
    expect(runInterpreterSource('se ("") { stampa("sim"); } senon { stampa("nao"); }'))
        ->toBe("nao" . PHP_EOL);
});

it('treats nullo as false', function () {
    expect(runInterpreterSource('se (nullo) { stampa("sim"); } senon { stampa("nao"); }'))
        ->toBe("nao" . PHP_EOL);
});

it('throws with a hint when a bare identifier naming a function is used as a condition', function () {
    runInterpreterSource('se (stampa) { stampa("sim"); }');
})->throws(TypeError::class, "'stampa' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?");

it('throws with a hint when a call result that is itself a function is used as a condition', function () {
    $environment = new Environment();
    $environment->globals['pegaFuncao'] = new Chiamabile('pegaFuncao', fn () => $environment->globals['stampa']);

    $scanner = new Scanner();
    $scanner->init('test.cod', 'se (pegaFuncao()) { stampa("sim"); }');

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(TypeError::class, "'stampa' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?");
});

it('throws with a hint when an assignment to a function value is used as a condition', function () {
    runInterpreterSource('sia x = nullo; se (x = stampa) { stampa("sim"); }');
})->throws(TypeError::class, "'stampa' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?");

it('throws with a hint when a variable declared with a function value is used as a condition', function () {
    runInterpreterSource('se (sia f = stampa) { stampa("sim"); }');
})->throws(TypeError::class, "'stampa' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?");

it('throws when a condition evaluates to a value with no boolean conversion', function () {
    $fakeType = new class implements \App\Types\Type {
        public function __toString()
        {
            return '<fake>';
        }
    };

    $environment = new Environment();
    $environment->globals['valorFalso'] = new Chiamabile('valorFalso', fn () => $fakeType);

    $scanner = new Scanner();
    $scanner->init('test.cod', 'se (valorFalso()) { stampa("sim"); }');

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();
    $class = get_class($fakeType);

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(Exception::class, "Impossibile convertire '{$class}' in booleano.\n");
});

it('runs every statement inside a block in order', function () {
    expect(runInterpreterSource('{ stampa("um"); stampa("dois"); }'))
        ->toBe("um" . PHP_EOL . "dois" . PHP_EOL);
});

it('scopes a variable declared inside a block to that block', function () {
    runInterpreterSource('se (vero) { sia segreto = 1; } stampa(segreto);');
})->throws(RuntimeError::class, "Identificatore 'segreto' non definito.");

it('allows a block to shadow an outer variable without changing it', function () {
    $source = 'sia x = 1; se (vero) { sia x = 2; stampa(x); } stampa(x);';

    expect(runInterpreterSource($source))->toBe(2 . PHP_EOL . 1 . PHP_EOL);
});

it('lets an assignment inside a block reach an outer variable', function () {
    expect(runInterpreterSource('sia x = 1; se (vero) { x = 2; } stampa(x);'))
        ->toBe(2 . PHP_EOL);
});

it('lets a nested block reach a variable declared several scopes above', function () {
    expect(runInterpreterSource('sia x = 1; se (vero) { se (vero) { x = 2; } } stampa(x);'))
        ->toBe(2 . PHP_EOL);
});

it('throws when assigning inside a block to a constant declared in an outer scope', function () {
    runInterpreterSource('cost PI = 3; se (vero) { PI = 4; }');
})->throws(RuntimeError::class, "Impossibile riassegnare la costante 'PI'.");

it('throws when assigning inside a block to an identifier that was never declared', function () {
    runInterpreterSource('se (vero) { fantasma = 1; }');
})->throws(RuntimeError::class, "Identificatore 'fantasma' non definito.");

it('wraps a native bool return value from a builtin into a Booleano', function () {
    $environment = new Environment();
    $environment->globals['ehVero'] = new Chiamabile('ehVero', fn () => true);

    $scanner = new Scanner();
    $scanner->init('test.cod', 'stampa(ehVero());');

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();

    ob_start();
    $interpreter->run($program, $environment);
    $output = ob_get_clean();

    expect($output)->toBe('vero' . PHP_EOL);
});

it('throws when a builtin returns a native value with no Codice equivalent', function () {
    $environment = new Environment();
    $environment->globals['lista'] = new Chiamabile('lista', fn () => ['a', 'b']);

    $scanner = new Scanner();
    $scanner->init('test.cod', 'lista();');

    $parser = new Parser();
    $parser->init($scanner->scan());
    $program = $parser->parse();

    $interpreter = new Interpreter();

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(Exception::class, "Impossibile convertire il valore nativo di tipo 'array' in un Type di Codice.\n");
});

it('throws when a unary operator has no implementation', function () {
    $loc = new Loc();
    $op = new Token(TokenType::STAR, '*', $loc);
    $expr = new UnaryExpr($op, new IntLiteral(new Token(TokenType::INT, 1, $loc)));
    $program = new Program([new ExprStatement($expr)]);

    $interpreter = new Interpreter();
    $environment = new Environment();

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(Exception::class, "Operatore unario '*' non implementato.\n");
});

it('throws when a binary operator has no implementation', function () {
    $loc = new Loc();
    $op = new Token(TokenType::ASSIGN, '=', $loc);
    $left = new IntLiteral(new Token(TokenType::INT, 1, $loc));
    $right = new IntLiteral(new Token(TokenType::INT, 2, $loc));
    $expr = new BinaryExpr($left, $op, $right);
    $program = new Program([new ExprStatement($expr)]);

    $interpreter = new Interpreter();
    $environment = new Environment();

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(Exception::class, "Operatore binario '=' non implementato.\n");
});

it('throws when an expression has no implementation', function () {
    $expr = new class implements Expr {};
    $program = new Program([new ExprStatement($expr)]);

    $interpreter = new Interpreter();
    $environment = new Environment();

    $class = get_class($expr);

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(Exception::class, "Espressione inattesa '{$class}'.\n");
});

it('throws when a statement has no implementation', function () {
    $statement = new class implements Stmt {};
    $program = new Program([$statement]);

    $interpreter = new Interpreter();
    $environment = new Environment();

    $class = get_class($statement);

    expect(fn () => $interpreter->run($program, $environment))
        ->toThrow(Exception::class, "Istruzione inattesa '{$class}'.\n");
});
