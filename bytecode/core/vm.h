#ifndef CODICE_VM_H
#define CODICE_VM_H

#include <stdint.h>

#include "types.h"

void pushVM(VM *vm, Value value);
Value popVM(VM *vm);
void initVM(VM *vm, uint32_t initial_capacity);
void freeVM(VM *vm);
void freeConstantPool(Value *pool, uint16_t pool_size);

#endif
