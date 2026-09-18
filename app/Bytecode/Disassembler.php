<?php

namespace App\Bytecode;

use App\Enums\Operation;
use App\Enums\VMType;
use Exception;

class Disassembler
{
    private const OP_OPERAND_SIZE = [
        Operation::LOAD_CONST->value  => 2, // uint16 pool index
        Operation::STORE_LOCAL->value => 2, // uint16 symbol index
        Operation::LOAD_LOCAL->value  => 2, // uint16 symbol index
        Operation::ADD->value         => 0,
        Operation::CALL_FUNC->value   => 0,
    ];

    /** @var resource */
    private $fp;

    public function disassemble(string $path): void
    {
        if (!is_file($path)) {
            throw new Exception("file not found: {$path}");
        }

        $this->fp = fopen($path, 'rb');
        $size = filesize($path);

        try {
            $this->printHeader($path, $size);
            $pool = $this->printConstantPool();
            $this->printLocals();
            $this->printInstructions($pool);
        } finally {
            fclose($this->fp);
        }
    }

    private function printHeader(string $path, int $size): void
    {
        $sig = fread($this->fp, 3);
        if ($sig === false || strlen($sig) < 3) {
            throw new Exception("unexpected EOF reading magic number");
        }
        $version = $this->readU8();

        $sigHex = strtoupper(bin2hex($sig));

        printf("=== Header ===\n");
        printf("File:    %s (%d bytes)\n", $path, $size);
        printf("Magic:   0x%s\n", $sigHex);
        printf("Version: %d\n", $version);

        if ($sigHex !== 'ADA1A5') {
            printf("         WARNING: unexpected magic number (expected 0xADA1A5)\n");
        }
    }

    private function printConstantPool(): array
    {
        $poolSize = $this->readU16();
        printf("\n=== Constant Pool (%d) ===\n", $poolSize);

        $pool = [];
        for ($i = 0; $i < $poolSize; $i++) {
            $type = $this->readU8();
            $vmType = VMType::tryFrom($type);
            $typeName = $vmType?->name ?? sprintf('UNKNOWN(0x%02X)', $type);

            $value = match ($vmType) {
                VMType::INT => (string) $this->readU32(),
                VMType::STR => $this->readStringConstant(),
                default => throw new Exception("unknown constant pool type 0x" . dechex($type) . " at index {$i}"),
            };

            $pool[$i] = $value;
            printf("  [%d] %-5s %s\n", $i, $typeName, $value);
        }

        return $pool;
    }

    private function readStringConstant(): string
    {
        $len = $this->readU32();
        $str = $len > 0 ? fread($this->fp, $len) : '';
        if ($str === false || strlen($str) < $len) {
            throw new Exception("unexpected EOF reading string constant");
        }
        return '"' . $str . '"';
    }

    private function printLocals(): void
    {
        $localsCount = $this->readU16();
        printf("\nLocals: %d\n", $localsCount);
    }

    private function printInstructions(array $pool): void
    {
        printf("\n=== Instructions ===\n");

        while (true) {
            $offset = ftell($this->fp);
            $opByte = fread($this->fp, 1);
            if ($opByte === false || strlen($opByte) < 1) break;
            $op = ord($opByte);

            $operation = Operation::tryFrom($op);
            $opName = $operation?->name ?? sprintf('UNKNOWN(0x%02X)', $op);
            $operandSize = self::OP_OPERAND_SIZE[$op] ?? 0;

            $operandStr = '';
            $comment = '';
            if ($operandSize === 2) {
                $operand = $this->readU16();
                $operandStr = "#{$operand}";
                if ($operation === Operation::LOAD_CONST && isset($pool[$operand])) {
                    $comment = "; {$pool[$operand]}";
                }
            }

            printf("%04X  %-12s %-6s %s\n", $offset, $opName, $operandStr, $comment);

            if ($operation === null) {
                printf("         WARNING: unknown opcode, stopping disassembly\n");
                break;
            }
        }
    }

    private function readU8(): int
    {
        $data = fread($this->fp, 1);
        if ($data === false || strlen($data) < 1) throw new Exception("unexpected EOF reading u8");
        return ord($data);
    }

    private function readU16(): int
    {
        $data = fread($this->fp, 2);
        if ($data === false || strlen($data) < 2) throw new Exception("unexpected EOF reading u16");
        return unpack("n", $data)[1];
    }

    private function readU32(): int
    {
        $data = fread($this->fp, 4);
        if ($data === false || strlen($data) < 4) throw new Exception("unexpected EOF reading u32");
        return unpack("N", $data)[1];
    }
}
