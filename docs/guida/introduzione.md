# Cos'è Codice?

Codice è un piccolo linguaggio di programmazione con parole chiave in italiano. Non ha ambizioni reali — esiste puramente come progetto di studio, un modo per imparare come si costruiscono i linguaggi di programmazione (lexer, parser, interprete) praticando allo stesso tempo il vocabolario italiano.

Scritto in PHP, è volutamente semplice e incompleto in alcuni punti. Aspettati bordi grezzi.

## Il primo programma

```cod
stampa("Ciao, mondo!")
```

```sh
$ php codice.php examples/ciao_mondo.cod
Ciao, mondo!
```

## Come funziona

Il codice sorgente percorre tre fasi prima di essere eseguito:

1. **Lexer** — trasforma il testo in una sequenza di token
2. **Parser** — costruisce un AST (albero sintattico astratto) dai token
3. **Interprete** — percorre l'AST ed esegue ogni nodo direttamente

## Stato attuale

Il progetto è nelle prime fasi. La pipeline gira end-to-end: il lexer tokenizza il sorgente, il parser costruisce l'AST, e l'interprete lo esegue con un tree-walker.

Sono supportati: letterali, identificatori, variabili, chiamate a funzione (`stampa` è l'unica built-in) e espressioni aritmetiche.

::: info
Nulla qui è da considerarsi stabile o pronto per la produzione.
:::
