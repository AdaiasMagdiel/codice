# Funzioni built-in

Codice include attualmente una sola funzione built-in.

## stampa

Stampa un valore sullo standard output, seguito da una nuova riga.

**Sintassi**

```cod
stampa(valore)
```

**Esempi**

```cod
stampa("Ciao, mondo!")   → Ciao, mondo!
stampa(42)               → 42
stampa(3.14)             → 3.14
stampa(vero)             → vero
stampa(nullo)            → nullo
```

Le variabili e le espressioni sono accettate come argomento:

```cod
sia x = 10
stampa(x)          → 10
stampa(x * 2 + 1)  → 21
stampa("x vale " + x)  → x vale 10
```

::: info
Il valore restituito da `stampa` è sempre `nullo`.
:::
