<?php

use App\Types\Booleano;
use App\Types\Decimale;
use App\Types\Intero;
use App\Types\Nullo;
use App\Types\Stringa;
use App\Types\Type;

it('converts a Stringa to its raw value', function () {
    $stringa = new Stringa('ciao');

    expect($stringa)->toBeInstanceOf(Type::class)
        ->and((string) $stringa)->toBe('ciao');
});

it('converts a true Booleano to "vero"', function () {
    $booleano = new Booleano(true);

    expect($booleano)->toBeInstanceOf(Type::class)
        ->and((string) $booleano)->toBe('vero');
});

it('converts a false Booleano to "falso"', function () {
    $booleano = new Booleano(false);

    expect((string) $booleano)->toBe('falso');
});

it('converts a Nullo to "nullo"', function () {
    $nullo = new Nullo();

    expect($nullo)->toBeInstanceOf(Type::class)
        ->and((string) $nullo)->toBe('nullo');
});

it('converts an Intero to its raw value', function () {
    $intero = new Intero(42);

    expect($intero)->toBeInstanceOf(Type::class)
        ->and((string) $intero)->toBe('42');
});

it('converts a Decimale to its raw value', function () {
    $decimale = new Decimale(3.14);

    expect($decimale)->toBeInstanceOf(Type::class)
        ->and((string) $decimale)->toBe('3.14');
});

it('converts a whole-number Decimale keeping the decimal point', function () {
    $decimale = new Decimale(67.0);

    expect((string) $decimale)->toBe('67.0');
});
