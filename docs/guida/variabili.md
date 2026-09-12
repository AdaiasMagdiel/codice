# Variabili

Le variabili in Codice si dichiarano con la parola chiave `sia`, seguita da un identificatore e da un valore.

## Dichiarazione

```cod
sia nome   = "Marco"
sia eta    = 30
sia pi     = 3.14
sia attivo = vero
```

Una variabile viene creata al momento della prima assegnazione. Codice ne deduce il tipo automaticamente dal valore — non è necessario dichiararlo esplicitamente.

## Tipi inferiti

| Valore | Tipo dedotto | Parola chiave |
|--------|-------------|--------------|
| `"testo"` | Stringa | `stringa` |
| `42` | Intero | `intero` |
| `3.14` | Decimale | `decimale` |
| `vero` / `falso` | Booleano | `booleano` |
| `nullo` | Nullo | `nullo` |

## Utilizzo

Le variabili possono essere usate in qualsiasi espressione:

```cod
sia x = 10
sia y = 3

stampa(x + y)     → 13
stampa(x * y)     → 30
stampa(x / y)     → 3.3333333333333
```

## Riassegnazione

Una variabile può essere riassegnata con `=`, a patto che il nuovo valore sia dello stesso tipo:

```cod
sia contatore = 0
contatore = contatore + 1
stampa(contatore)  → 1
```

::: warning Tipi fissi
Il tipo di una variabile è determinato alla dichiarazione e non può cambiare. Assegnare un valore di tipo diverso è un errore.
:::

## Identificatori

Un identificatore è una sequenza di lettere, cifre e underscore `_`, che non inizia con una cifra. Per convenzione si usa lo snake_case.

```cod
sia nome_utente = "luca"
sia eta2        = 25
sia _privato    = nullo
```
