# Il REPL

Il REPL (Read-Eval-Print Loop) di Codice permette di eseguire istruzioni interattivamente, una alla volta.

## Avvio

```sh
php codice.php
```

## Utilizzo

```
$ php codice.php
>>> stampa("Ciao, mondo!")
Ciao, mondo!
>>> sia x = 10
>>> stampa(x + 5)
15
>>> esci
Ciao!
```

Ogni istruzione viene eseguita immediatamente nello stesso ambiente, quindi lo stato — variabili e valori — viene mantenuto tra una riga e l'altra.

## Uscire dal REPL

| Metodo | Descrizione |
|--------|-------------|
| `esci` | Comando esplicito di uscita |
| `Ctrl+C` | Interruzione del processo |

::: tip
Il REPL è il posto ideale per sperimentare. Prova espressioni, assegna variabili, chiama `stampa` — tutto viene valutato subito.
:::
