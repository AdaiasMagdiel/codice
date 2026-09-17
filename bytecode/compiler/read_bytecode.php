<?php

use App\Enums\Operation;
use App\Enums\VMType;

require_once __DIR__ . '/../../vendor/autoload.php';

// operand size in bytes for each opcode (0 = none), unknown ops default to 0
const OP_OPERAND_SIZE = [
    Operation::LOAD_CONST->value  => 2, // uint16 pool index
    Operation::STORE_LOCAL->value => 2, // uint16 symbol index
    Operation::LOAD_LOCAL->value  => 2, // uint16 symbol index
    Operation::ADD->value         => 0,
    Operation::CALL_FUNC->value   => 0,
];

function fail(string $msg): never
{
    fwrite(STDERR, "Error: {$msg}\n");
    exit(1);
}

function read_u8($fp): int
{
    $data = fread($fp, 1);
    if ($data === false || strlen($data) < 1) fail("unexpected EOF reading u8");
    return ord($data);
}

function read_u16($fp): int
{
    $data = fread($fp, 2);
    if ($data === false || strlen($data) < 2) fail("unexpected EOF reading u16");
    return unpack("n", $data)[1];
}

function read_u32($fp): int
{
    $data = fread($fp, 4);
    if ($data === false || strlen($data) < 4) fail("unexpected EOF reading u32");
    return unpack("N", $data)[1];
}

$path = $argv[1] ?? null;
if ($path === null) {
    fail("usage: php read_bytecode.php <file.codc>");
}
if (!is_file($path)) {
    fail("file not found: {$path}");
}

$fp = fopen($path, 'rb');
$size = filesize($path);

$sig = fread($fp, 3);
if ($sig === false || strlen($sig) < 3) fail("unexpected EOF reading magic number");
$version = read_u8($fp);

$sigHex = strtoupper(bin2hex($sig));

printf("=== Header ===\n");
printf("File:    %s (%d bytes)\n", $path, $size);
printf("Magic:   0x%s\n", $sigHex);
printf("Version: %d\n", $version);

if ($sigHex !== 'ADA1A5') {
    printf("         WARNING: unexpected magic number (expected 0xADA1A5)\n");
}

$poolSize = read_u16($fp);
printf("\n=== Constant Pool (%d) ===\n", $poolSize);

$pool = [];
for ($i = 0; $i < $poolSize; $i++) {
    $type = read_u8($fp);
    $vmType = VMType::tryFrom($type);
    $typeName = $vmType?->name ?? sprintf('UNKNOWN(0x%02X)', $type);

    $value = match ($vmType) {
        VMType::INT => (string) read_u32($fp),
        VMType::STR => (function () use ($fp) {
            $len = read_u32($fp);
            $str = $len > 0 ? fread($fp, $len) : '';
            if ($str === false || strlen($str) < $len) fail("unexpected EOF reading string constant");
            return '"' . $str . '"';
        })(),
        default => fail("unknown constant pool type 0x" . dechex($type) . " at index {$i}"),
    };

    $pool[$i] = $value;
    printf("  [%d] %-5s %s\n", $i, $typeName, $value);
}

$localsCount = read_u16($fp);
printf("\nLocals: %d\n", $localsCount);

printf("\n=== Instructions ===\n");

while (true) {
    $offset = ftell($fp);
    $opByte = fread($fp, 1);
    if ($opByte === false || strlen($opByte) < 1) break;
    $op = ord($opByte);

    $operation = Operation::tryFrom($op);
    $opName = $operation?->name ?? sprintf('UNKNOWN(0x%02X)', $op);
    $operandSize = OP_OPERAND_SIZE[$op] ?? 0;

    $operandStr = '';
    $comment = '';
    if ($operandSize === 2) {
        $operand = read_u16($fp);
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

fclose($fp);
