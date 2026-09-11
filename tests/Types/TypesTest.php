<?php

use App\Types\Booleano;
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
