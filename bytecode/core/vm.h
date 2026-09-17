#ifndef CODICE_VM_H
#define CODICE_VM_H

#include <stdint.h>

#include "types.h"

void initVM(VM *vm, uint32_t initial_capacity, uint16_t locals_count);
void pushVM(VM *vm, Value value);
Value popVM(VM *vm);
void freeVM(VM *vm);
void freeConstantPool(Value *pool, uint16_t pool_size);
void runtime_error(VM *vm, Value *constant_pool, uint16_t pool_size, const char *fmt, ...);

#endif
