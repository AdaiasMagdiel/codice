<?php

namespace App;

use App\Exceptions\CodiceError;
use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Runtime\Interpreter;
use Throwable;

class Codice
{
	private Scanner $scanner;
	private Parser $parser;
	private Interpreter $interpreter;
	private Environment $environment;

	public function __construct()
	{
		$this->scanner = new Scanner();
		$this->parser = new Parser();
		$this->interpreter = new Interpreter();
		$this->environment = new Environment();
	}

	public function runREPL() {}

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

			$program = $this->parser->parse();

			$this->interpreter->run($program, $this->environment);
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
