# Operazioni matematiche

Codice supporta le quattro operazioni aritmetiche fondamentali, con la precedenza standard degli operatori e il raggruppamento tramite parentesi.

## Operatori

| Operatore | Nome | Esempio | Risultato |
|-----------|------|---------|-----------|
| `+` | Addizione | `2 + 3` | `5` |
| `-` | Sottrazione | `10 - 3` | `7` |
| `*` | Moltiplicazione | `4 * 3` | `12` |
| `/` | Divisione | `5 / 2` | `2.5` |
| `+x` | Più unario | `+5` | `5` |
| `-x` | Meno unario | `-5` | `-5` |

## Esempi

```cod
stampa(2 + 3)    → 5
stampa(10 - 3)   → 7
stampa(4 * 3)    → 12
stampa(5 / 2)    → 2.5
```

## Precedenza degli operatori

La moltiplicazione e la divisione hanno precedenza sull'addizione e la sottrazione — come nell'aritmetica standard.

```cod
stampa(2 + 3 * 4)    → 14
stampa((2 + 3) * 4)  → 20
```

Le parentesi sovrascrivono qualsiasi precedenza.

## Divisione

La divisione restituisce sempre un `decimale`, anche quando entrambi gli operandi sono interi.

```cod
stampa(10 / 4)   → 2.5
stampa(9 / 3)    → 3.0
```

## Operatori unari

```cod
sia x = 5
stampa(-x)    → -5
stampa(+x)    → 5
stampa(-(-x)) → 5
```

## Stringhe

L'operatore `+` concatena stringhe quando almeno uno degli operandi è una `stringa`. L'operatore `*` ripete una stringa quando l'altro operando è un `intero`.

```cod
stampa("ciao" + " " + "mondo")  → ciao mondo
stampa("ab" * 3)                 → ababab
```

Vedi [Stringhe](/guida/stringhe) per altri dettagli.
