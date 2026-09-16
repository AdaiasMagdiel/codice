#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#include <string.h>

#include "core/types.h"
#include "core/vm.h"
#include "core/reader.h"
#include "core/builtins.h"

int main()
{
    VM vm;
    unsigned char MAGIC_NUMBER[3] = {0xAD, 0xA1, 0xA5};

    FILE *fp = fopen("./output/program.codb", "rb");
    if (fp == NULL)
    {
        fprintf(stderr, "Error: File not found.\n");
        return -1;
    }
    fseek(fp, 0, SEEK_END);
    int file_size = ftell(fp);
    rewind(fp);

    unsigned char buffer[4];
    if (fread(buffer, 1, 4, fp) < 4)
    {
        fprintf(stderr, "Error: Failed to read file header.\n");
        fclose(fp);
        exit(1);
    }

    // Check magic number
    if (memcmp(buffer, MAGIC_NUMBER, 3) != 0)
    {
        fprintf(stderr, "Error: File is not a codice bytecode file.\n");
        fclose(fp);
        exit(1);
    }

    // Check version
    if (buffer[3] != 0x01)
    {
        fprintf(stderr, "Error: Unsupported version: %d.\n", buffer[3]);
        fclose(fp);
        exit(1);
    }

    uint16_t pool_size = read_u16(fp);
    Value *constant_pool = malloc(sizeof(Value) * pool_size);
    if (constant_pool == NULL)
    {
        fprintf(stderr, "Error: Failed to allocate memory for the constant pool.\n");
        fclose(fp);
        exit(1);
    }

    // ----- Constant pool
    for (int i = 0; i < pool_size; i++)
    {
        constant_pool[i].type = read_u8(fp);

        if (constant_pool[i].type == TYPE_INT)
        {
            constant_pool[i].as.as_int = read_u32(fp);
        }
        else if (constant_pool[i].type == TYPE_STRING)
        {
            uint32_t length = read_u32(fp);

            constant_pool[i].as.as_string = malloc(length + 1);
            if (constant_pool[i].as.as_string == NULL)
            {
                fclose(fp);
                freeConstantPool(constant_pool, pool_size);

                fprintf(stderr, "Error: Failed to allocate memory for a string constant.\n");
                exit(1);
            }

            if (fread(constant_pool[i].as.as_string, 1, length, fp) < length)
            {
                fclose(fp);
                freeConstantPool(constant_pool, pool_size);

                fprintf(stderr, "Error: Failed to read a string constant from the file.\n");
                exit(1);
            }
            constant_pool[i].as.as_string[length] = '\0';
        }
        else
        {
            fclose(fp);
            freeConstantPool(constant_pool, pool_size);

            fprintf(stderr, "Error: Type '%d' not implemented.\n", constant_pool[i].type);
            exit(1);
        }

        file_size--;
    }
    // Constant pool -----

    // --- Instructions
    initVM(&vm, 256);

    while (1)
    {
        uint8_t op;
        if (fread(&op, 1, 1, fp) < 1)
        {
            break;
        }

        if (op == OP_LOAD_CONST)
        {
            uint16_t index = read_u16(fp);
            pushVM(&vm, constant_pool[index]);
        }
        else if (op == OP_CALL_FUNC)
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
                fclose(fp);
                freeConstantPool(constant_pool, pool_size);
                freeVM(&vm);

                fprintf(stderr, "Error: Function '%s' not found.\n", callee.as.as_string);
                exit(1);
            }
        }
        else
        {
            fclose(fp);
            freeConstantPool(constant_pool, pool_size);
            freeVM(&vm);

            fprintf(stderr, "Error: Operation '%d' not implemented.\n", op);
            exit(1);
        }
    }
    // Instructions ---

    fclose(fp);
    freeConstantPool(constant_pool, pool_size);
    freeVM(&vm);

    return 0;
}
