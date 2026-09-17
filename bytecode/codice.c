#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#include <string.h>

#include "core/types.h"
#include "core/vm.h"
#include "core/reader.h"
#include "core/builtins.h"
#include "core/ops/add.h"

void init_constant_pool(FILE *fp, Value **out_pool, uint16_t *out_pool_size)
{
    uint16_t pool_size = read_u16(fp);
    Value *pool = malloc(sizeof(Value) * pool_size);

    if (pool == NULL)
    {
        fprintf(stderr, "Error: Failed to allocate memory for the constant pool.\n");
        fclose(fp);
        exit(1);
    }

    for (int i = 0; i < pool_size; i++)
    {
        pool[i].type = read_u8(fp);

        if (pool[i].type == TYPE_INT)
        {
            pool[i].as.as_int = read_u32(fp);
        }
        else if (pool[i].type == TYPE_STRING)
        {
            uint32_t length = read_u32(fp);

            pool[i].as.as_string = malloc(length + 1);
            if (pool[i].as.as_string == NULL)
            {
                fclose(fp);
                freeConstantPool(pool, pool_size);

                fprintf(stderr, "Error: Failed to allocate memory for a string constant.\n");
                exit(1);
            }

            if (fread(pool[i].as.as_string, 1, length, fp) < length)
            {
                fclose(fp);
                freeConstantPool(pool, pool_size);

                fprintf(stderr, "Error: Failed to read a string constant from the file.\n");
                exit(1);
            }
            pool[i].as.as_string[length] = '\0';
        }
        else
        {
            fclose(fp);
            freeConstantPool(pool, pool_size);

            fprintf(stderr, "Error: Type '%d' not implemented.\n", pool[i].type);
            exit(1);
        }
    }

    *out_pool = pool;
    *out_pool_size = pool_size;
}

int init_bytecode(FILE *fp, uint8_t **out_bytecode, uint32_t *out_bytecode_count)
{
    uint32_t start = ftell(fp);
    fseek(fp, 0, SEEK_END);
    uint32_t bytecode_count = ftell(fp) - start;

    fseek(fp, start, SEEK_SET);

    uint8_t *bytecode = malloc(bytecode_count);
    if (bytecode == NULL)
    {
        return 0;
    }

    fread(bytecode, 1, bytecode_count, fp);

    *out_bytecode = bytecode;
    *out_bytecode_count = bytecode_count;

    return 1;
}

int main(int argc, char **argv)
{
    VM vm;
    unsigned char MAGIC_NUMBER[3] = {0xAD, 0xA1, 0xA5};

    const char *path = argc > 1 ? argv[1] : "./output/program.codc";

    FILE *fp = fopen(path, "rb");
    if (fp == NULL)
    {
        fprintf(stderr, "Error: File '%s' not found.\n", path);
        return -1;
    }

    // --- Check magic number
    unsigned char buffer[4];
    if (fread(buffer, 1, 4, fp) < 4)
    {
        fprintf(stderr, "Error: Failed to read file header.\n");
        fclose(fp);
        exit(1);
    }

    if (memcmp(buffer, MAGIC_NUMBER, 3) != 0)
    {
        fprintf(stderr, "Error: File is not a codice bytecode file.\n");
        fclose(fp);
        exit(1);
    }

    // --- Check version
    if (buffer[3] != 0x01)
    {
        fprintf(stderr, "Error: Unsupported version: %d.\n", buffer[3]);
        fclose(fp);
        exit(1);
    }

    // --- Constant Pool

    Value *constant_pool;
    uint16_t pool_size;
    init_constant_pool(fp, &constant_pool, &pool_size);

    // --- Bytecode

    uint16_t locals_count = read_u16(fp);
    initVM(&vm, 256, locals_count);

    uint8_t *bytecode;
    uint32_t bytecode_count;
    int success = init_bytecode(fp, &bytecode, &bytecode_count);
    fclose(fp);
    if (!success)
        runtime_error(&vm, constant_pool, pool_size, "Error: Failed to allocate memory for the bytecode.");

    // --- Instructions

    uint32_t ip = 0;

    while (ip < bytecode_count)
    {
        uint8_t op = bytecode[ip++];
        uint16_t index;

        switch (op)
        {
        case OP_LOAD_CONST: // 0x01
            index = read_u16_bc(bytecode, &ip);
            pushVM(&vm, constant_pool[index]);
            break;

        case OP_STORE_LOCAL: // 0x02
            index = read_u16_bc(bytecode, &ip);
            vm.locals[index] = popVM(&vm);
            break;

        case OP_LOAD_LOCAL: // 0x03
            index = read_u16_bc(bytecode, &ip);
            pushVM(&vm, vm.locals[index]);
            break;

        case OP_ADD: // 0x04
        {
            int res = op_add(&vm);

            if (res == -1)
                runtime_error(&vm, constant_pool, pool_size, "Error: Failed to allocate memory for string concatenation.");
            if (res == -2)
                runtime_error(&vm, constant_pool, pool_size, "Error: ADD requires both operands to be either INT or STRING.");

            break;
        }

        case OP_CALL_FUNC: // 0x05
        {
            Value callee = popVM(&vm);
            Value args_count = popVM(&vm);

            BuiltinFn builtin = find_builtin(callee.as.as_string);

            if (builtin != NULL)
            {
                Value result = builtin((uint32_t)args_count.as.as_int, &vm);
                pushVM(&vm, result);
            }
            else
            {
                runtime_error(&vm, constant_pool, pool_size, "Error: Function '%s' not found.", callee.as.as_string);
            }
            break;
        }

        default:
            runtime_error(&vm, constant_pool, pool_size, "Error: Operation '%d' not implemented.", op);
            break;
        }
    }
    // Instructions ---

    freeConstantPool(constant_pool, pool_size);
    freeVM(&vm);

    return 0;
}
