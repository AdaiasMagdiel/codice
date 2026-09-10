<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Codice;

$codice = new Codice();

function usage(?int $exitCode = null): void
{
	echo "Uso:\n";
	echo "  php codice.php [FILE]\n\n";

	echo "Argomenti:\n";
	echo "  FILE        Percorso di un file .cdc o .codice da eseguire.\n";
	echo "              Se omesso, avvia il REPL di Codice.\n\n";

	echo "Esempi:\n";
	echo "  $ php codice.php                            # Avvia il REPL\n";
	echo "  $ php codice.php examples/ciao_mondo.cdc    # Esegue l'esempio ciao mondo\n";

	if (!is_null($exitCode)) exit($exitCode);
}

if (PHP_SAPI !== 'cli') {
	if (http_response_code()) {
		http_response_code(405);
	}
	echo "Errore: Questo script può essere eseguito solo da riga di comando (CLI).\n";
	exit(1);
}

if ($argc === 1) {
	echo "REPL non ancora implementato.\n";
	exit(0);
}

$filepath = $argv[1];
exit($codice->runFile($filepath));
