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

	public function runREPL()
	{
		while (true) {
			$line = readline(">>> ");

			if ($line === false) {
				echo "\nCiao!\n";
				break;
			}

			$line = trim($line);

			if ($line === 'esci') {
				echo "Ciao!\n";
				break;
			}

			if ($line === '') {
				continue;
			}

			readline_add_history($line);

			if (!str_ends_with($line, ';')) {
				$line .= ';';
			}

			$this->run('stdin', $line);
		}
	}

	public function runFile(string $filePath): int
	{
		if (!is_file($filePath)) {
			echo "Errore: Il file '$filePath' non esiste.\n";
			return 1;
		}

		$content = file_get_contents($filePath);
		return $this->run(basename($filePath), $content);
	}

	public function run(string $file, string $content): int
	{
		try {
			$this->scanner->init($file, $content);

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
