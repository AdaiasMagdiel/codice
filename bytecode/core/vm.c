#include <stdarg.h>
#include <stdio.h>
#include <stdlib.h>

#include "vm.h"

void initVM(VM *vm, uint32_t initial_capacity, uint16_t locals_count)
{
    vm->capacity = initial_capacity;
    vm->stack = (Value *)malloc(sizeof(Value) * vm->capacity);

    if (vm->stack == NULL)
    {
        fprintf(stderr, "Error: failed to allocate memory for the stack!\n");
        exit(1);
    }

    vm->top = vm->stack;

    vm->locals_count = locals_count;
    vm->locals = malloc(sizeof(Value) * locals_count);

    if (vm->locals == NULL)
    {
        fprintf(stderr, "Error: failed to allocate memory for the locals!\n");
        exit(1);
    }
}

void pushVM(VM *vm, Value value)
{
    uint32_t current_size = vm->top - vm->stack;
    if (current_size >= vm->capacity)
    {
        vm->capacity *= 2;
        vm->stack = realloc(vm->stack, sizeof(Value) * vm->capacity);
        vm->top = vm->stack + current_size;
    }
    *vm->top = value;
    vm->top++;
}

Value popVM(VM *vm)
{
    if (vm->top <= vm->stack)
    {
        fprintf(stderr, "Erro: Stack Underflow!\n");
        exit(1);
    }

    vm->top--;

    return *vm->top;
}

void freeVM(VM *vm)
{
    free(vm->locals);
    free(vm->stack);
    vm->locals = NULL;
    vm->stack = NULL;
    vm->top = NULL;
    vm->locals_count = 0;
    vm->capacity = 0;
}

void freeConstantPool(Value *pool, uint16_t pool_size)
{
    if (pool == NULL)
        return;

    for (int i = 0; i < pool_size; i++)
    {
        if (pool[i].type == TYPE_STR && pool[i].as.as_string != NULL)
        {
            free(pool[i].as.as_string);
        }
    }

    free(pool);
}

void runtime_error(VM *vm, Value *constant_pool, uint16_t pool_size, const char *fmt, ...)
{
    va_list args;
    va_start(args, fmt);
    vfprintf(stderr, fmt, args);
    va_end(args);
    fprintf(stderr, "\n");

    freeConstantPool(constant_pool, pool_size);
    freeVM(vm);

    exit(1);
}
