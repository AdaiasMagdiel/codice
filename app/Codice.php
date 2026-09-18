<?php

namespace App;

use App\Bytecode\Disassembler;
use App\Exceptions\CodiceError;
use App\Lexer\Scanner;
use App\Parser\Parser;
use App\Runtime\Environment;
use App\Visitors\ByteCode;
use App\Visitors\Interpreter;
use Throwable;

class Codice
{
	private Scanner $scanner;
	private Parser $parser;
	private Interpreter $interpreter;

	public function __construct()
	{
		$this->scanner = new Scanner();
		$this->parser = new Parser();
		$this->interpreter = new Interpreter(new Environment());
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

			$program->accept($this->interpreter);
		} catch (CodiceError $e) {
			echo $e;
			return 1;
		} catch (Throwable $e) {
			echo $e->getMessage();
			return 1;
		}

		return 0;
	}

	public function compileFile(string $filePath, ?string $outputFile = null): int
	{
		if (!is_file($filePath)) {
			echo "Errore: Il file '$filePath' non esiste.\n";
			return 1;
		}

		$outputFile ??= ROOT_DIR . '/bytecode/output/' . pathinfo($filePath, PATHINFO_FILENAME) . '.codc';

		$outputDir = dirname($outputFile);
		if (!is_dir($outputDir)) {
			mkdir($outputDir, recursive: true);
		}

		try {
			$this->scanner->init(basename($filePath), file_get_contents($filePath));

			$tokens = $this->scanner->scan();
			$this->parser->init($tokens);

			$program = $this->parser->parse();

			$program->accept(new ByteCode($outputFile));
		} catch (CodiceError $e) {
			echo $e;
			return 1;
		} catch (Throwable $e) {
			echo $e->getMessage() . "\n";
			return 1;
		}

		echo "Bytecode scritto in '$outputFile'.\n";
		return 0;
	}

	public function disassembleFile(string $filePath): int
	{
		try {
			(new Disassembler())->disassemble($filePath);
		} catch (Throwable $e) {
			echo $e->getMessage() . "\n";
			return 1;
		}

		return 0;
	}
}
