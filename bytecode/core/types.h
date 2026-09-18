#ifndef CODICE_TYPES_H
#define CODICE_TYPES_H

#include <stdint.h>

#define TYPE_NULL 0x01
#define TYPE_INT 0x02
#define TYPE_STRING 0x03

#define OP_LOAD_CONST 0x01
#define OP_STORE_LOCAL 0x02
#define OP_LOAD_LOCAL 0x03
#define OP_ADD 0x04
#define OP_CALL_FUNC 0x05

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
    Value *top;
    uint32_t capacity;

    Value *locals;
    uint16_t locals_count;
} VM;

#endif
