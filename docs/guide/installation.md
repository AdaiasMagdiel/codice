# Installation

## Requirements

- PHP 8.1 or higher
- Composer

## Setup

Clone the repository and install dependencies:

```sh
git clone https://github.com/AdaiasMagdiel/codice.git
cd codice
composer install
```

## Usage

```sh
php codice.php [FILE]
```

| Argument | Description |
|----------|-------------|
| `FILE` | Path to a `.cod` file to run. If omitted, starts the REPL. |

### Running a file

```sh
php codice.php examples/ciao_mondo.cod
```

### Starting the REPL

```sh
php codice.php
```

## Tests

```sh
composer test           # Run the test suite (Pest)
composer test:coverage  # Run with coverage report (requires Xdebug)
```
