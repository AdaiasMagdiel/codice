#include <stdio.h>
#include <stdlib.h>

#include "vm.h"

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

void initVM(VM *vm, uint32_t initial_capacity)
{
    vm->capacity = initial_capacity;
    vm->stack = (Value *)malloc(sizeof(Value) * vm->capacity);

    if (vm->stack == NULL)
    {
        fprintf(stderr, "Erro ao alocar memória para a pilha!\n");
        exit(1);
    }

    vm->top = vm->stack;
}

void freeVM(VM *vm)
{
    free(vm->stack);
    vm->stack = NULL;
    vm->top = NULL;
    vm->capacity = 0;
}

void freeConstantPool(Value *pool, uint16_t pool_size)
{
    if (pool == NULL)
        return;

    for (int i = 0; i < pool_size; i++)
    {
        if (pool[i].type == TYPE_STRING && pool[i].as.as_string != NULL)
        {
            free(pool[i].as.as_string);
        }
    }

    free(pool);
}
