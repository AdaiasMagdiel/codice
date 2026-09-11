<?php

use App\Exceptions\CodiceError;
use App\Lexer\Loc;

beforeEach(function () {
    putenv('CODICE_NO_COLOR=1');
});

afterEach(function () {
    putenv('CODICE_NO_COLOR');
});

it('shows the error message', function () {
    $loc = new Loc('test.cod', 2, 3, [
        'stampa(nullo);',
        'stampa(42);',
        'stampa(1_000_000);',
    ], 1);

    $error = new CodiceError("Valore inatteso '4'", $loc);

    expect((string) $error)->toContain("Errore: Valore inatteso '4'");
});

it('shows the previous and next lines around the error', function () {
    $loc = new Loc('test.cod', 2, 3, [
        'stampa(nullo);',
        'stampa(42);',
        'stampa(1_000_000);',
    ], 1);

    $error = new CodiceError("Valore inatteso '4'", $loc);
    $output = (string) $error;

    expect($output)->toContain("test.cod:1   | stampa(nullo);")
        ->and($output)->toContain("test.cod:2:3 | stampa(42);")
        ->and($output)->toContain("test.cod:3   | stampa(1_000_000);");
});

it('aligns the indicator under the error column', function () {
    $loc = new Loc('test.cod', 2, 9, [
        'stampa(nullo);',
        'stampa(42);',
    ], 2);

    $error = new CodiceError("Valore inatteso '4'", $loc);
    $lines = explode("\n", (string) $error);

    $lineIndex = array_search('test.cod:2:9 | stampa(42);', $lines);

    expect($lineIndex)->not->toBeFalse()
        ->and($lines[$lineIndex + 1])->toBe(str_repeat(' ', 23) . '^~');
});

it('omits the previous line when the error is on the first line', function () {
    $loc = new Loc('test.cod', 1, 1, [
        'stampa(nullo);',
        'stampa(42);',
    ], 1);

    $error = new CodiceError("Valore inatteso '4'", $loc);
    $output = (string) $error;

    expect($output)->toContain("test.cod:1:1 | stampa(nullo);")
        ->and($output)->toContain("test.cod:2   | stampa(42);")
        ->and($output)->not->toContain('test.cod:0');
});

it('omits the next line when the error is on the last line', function () {
    $loc = new Loc('test.cod', 2, 1, [
        'stampa(nullo);',
        'stampa(42);',
    ], 1);

    $error = new CodiceError("Valore inatteso '4'", $loc);
    $output = (string) $error;

    expect($output)->toContain("test.cod:1   | stampa(nullo);")
        ->and($output)->toContain("test.cod:2:1 | stampa(42);")
        ->and($output)->not->toContain('test.cod:3');
});

it('does not include ANSI escape codes when CODICE_NO_COLOR is set', function () {
    $loc = new Loc('test.cod', 2, 3, [
        'stampa(nullo);',
        'stampa(42);',
        'stampa(1_000_000);',
    ], 1);

    $error = new CodiceError("Valore inatteso '4'", $loc);

    expect((string) $error)->not->toContain("\033[");
});
