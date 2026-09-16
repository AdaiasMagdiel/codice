#ifndef CODICE_TYPES_H
#define CODICE_TYPES_H

#include <stdint.h>

#define TYPE_NULL 0x01
#define TYPE_INT 0x02
#define TYPE_STRING 0x03

#define OP_LOAD_CONST 0x01
#define OP_PUSH 0x02
#define OP_ADD 0x03
#define OP_CALL_FUNC 0x04

typedef struct
{
    uint8_t type;
    union
    {
        int32_t as_int;
        char *as_string;
    } as;
} Value;

typedef struct
{
    Value *stack;
    uint32_t capacity;
    Value *top;
} VM;

#endif
