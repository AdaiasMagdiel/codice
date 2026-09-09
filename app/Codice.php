<?php

namespace App;

use App\Exceptions\CodiceError;
use App\Lexer\Scanner;
use Throwable;

class Codice
{
	private Scanner $scanner;

	public function __construct()
	{
		$this->scanner = new Scanner();
	}

	public function runFile(string $filePath): int
	{
		if (!is_file($filePath)) {
			echo "Errore: Il file '$filePath' non esiste.\n";
			return 1;
		}

		$content = file_get_contents($filePath);
		$this->scanner->init(basename($filePath), $content);

		try {
			$tokens = $this->scanner->scan();

			print_r($tokens);
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
