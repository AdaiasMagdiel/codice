<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Codice;

$codice = new Codice();

function usage(?int $exitCode = null): void
{
	echo "Uso:\n";
	echo "  php codice.php [FILE]\n";
	echo "  php codice.php -c|--compile FILE [OUTPUT]\n";
	echo "  php codice.php -d|--disasm FILE\n\n";

	echo "Argomenti:\n";
	echo "  FILE        Percorso di un file .cdc o .codice da eseguire.\n";
	echo "              Se omesso, avvia il REPL di Codice.\n\n";

	echo "Opzioni:\n";
	echo "  -c, --compile FILE [OUTPUT]  Compila FILE in bytecode (.codc).\n";
	echo "  -d, --disasm FILE            Disassembla un file di bytecode (.codc).\n\n";

	echo "Esempi:\n";
	echo "  $ php codice.php                            # Avvia il REPL\n";
	echo "  $ php codice.php examples/ciao_mondo.cod    # Esegue l'esempio ciao mondo\n";
	echo "  $ php codice.php -c examples/ciao_mondo.cod # Compila in bytecode\n";
	echo "  $ php codice.php -d ciao_mondo.codc         # Disassembla il bytecode\n";

	if (!is_null($exitCode)) exit($exitCode);
}

if (PHP_SAPI !== 'cli') {
	if (http_response_code()) {
		http_response_code(405);
	}
	echo "Errore: Questo script può essere eseguito solo da riga di comando (CLI).\n";
	exit(1);
}

$args = array_slice($argv, 1);

if (count($args) === 0) {
	$codice->runREPL();
	exit(0);
}

switch ($args[0]) {
	case '-c':
	case '--compile':
		if (!isset($args[1])) {
			echo "Errore: È necessario indicare il file da compilare.\n";
			usage(1);
		}
		exit($codice->compileFile($args[1], $args[2] ?? null));

	case '-d':
	case '--disasm':
		if (!isset($args[1])) {
			echo "Errore: È necessario indicare il file di bytecode da disassemblare.\n";
			usage(1);
		}
		exit($codice->disassembleFile($args[1]));

	case '-h':
	case '--help':
		usage(0);

	default:
		exit($codice->runFile($args[0]));
}
