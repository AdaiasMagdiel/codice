# Installazione

## Requisiti

- PHP 8.1 o superiore
- Composer

## Installazione

Clona il repository e installa le dipendenze:

```sh
git clone https://github.com/AdaiasMagdiel/codice.git
cd codice
composer install
```

## Utilizzo

```sh
php codice.php [FILE]
```

| Argomento | Descrizione |
|-----------|-------------|
| `FILE` | Percorso di un file `.cod` da eseguire. Se omesso, avvia il REPL. |

### Eseguire un file

```sh
php codice.php examples/ciao_mondo.cod
```

### Avviare il REPL

```sh
php codice.php
```

## Test

```sh
composer test           # Esegue la suite di test (Pest)
composer test:coverage  # Esegue con report di copertura (richiede Xdebug)
```
