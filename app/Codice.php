<?php

namespace App;

use App\Exceptions\CodiceError;
use App\Lexer\Scanner;
use App\Parser\Parser;
use Throwable;

class Codice
{
	private Scanner $scanner;
	private Parser $parser;

	public function __construct()
	{
		$this->scanner = new Scanner();
		$this->parser = new Parser();
	}

	public function runFile(string $filePath): int
	{
		if (!is_file($filePath)) {
			echo "Errore: Il file '$filePath' non esiste.\n";
			return 1;
		}

		try {
			$content = file_get_contents($filePath);
			$this->scanner->init(basename($filePath), $content);

			$tokens = $this->scanner->scan();
			$this->parser->init($tokens);

			$ast = $this->parser->parse();

			$content = print_r($ast, true);
			echo str_replace("    ", "..", $content) . PHP_EOL;
		} catch (CodiceError $e) {
			echo $e;
			return 1;
		} catch (Throwable $e) {
			echo $e->getMessage();
			return 1;
		}

		return 0;
	}
}
