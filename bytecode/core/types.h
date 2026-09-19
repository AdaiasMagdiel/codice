#ifndef CODICE_TYPES_H
#define CODICE_TYPES_H

#include <stdint.h>

#define TYPE_NULL        0x01
#define TYPE_INT         0x02
#define TYPE_FLOAT       0x03
#define TYPE_STR         0x04
#define TYPE_BOOL_TRUE   0x05
#define TYPE_BOOL_FALSE  0x06

#define OP_LOAD_CONST    0x01
#define OP_STORE_LOCAL   0x02
#define OP_LOAD_LOCAL    0x03
#define OP_ADD           0x04
#define OP_CALL_FUNC     0x05
#define OP_PUSH_TRUE     0x06
#define OP_PUSH_FALSE    0x07
#define OP_PUSH_NULL     0x08
#define OP_JUMP_IF_FALSE 0x09
#define OP_JUMP          0x0A

typedef struct
{
    uint8_t type;
    union
    {
        int32_t as_int;
        double as_float;
        char *as_string;
    } as;
} Value;

#define VAL_NULL()   ((Value) { .type = TYPE_NULL })
#define VAL_BOOL(b)  ((Value) { .type = (b) ? TYPE_BOOL_TRUE : TYPE_BOOL_FALSE })

typedef struct
{
    Value *stack;
    Value *top;
    uint32_t capacity;

    Value *locals;
    uint16_t locals_count;
} VM;

static inline int is_falsy(Value v) {
    switch (v.type) {
        case TYPE_BOOL_FALSE:
        case TYPE_NULL:
            return 1;

        case TYPE_INT:
            return v.as.as_int == 0;

        case TYPE_FLOAT:
            return v.as.as_float == 0.0;

        case TYPE_STR:
            return v.as.as_string == NULL || v.as.as_string[0] == '\0';

        case TYPE_BOOL_TRUE:
        default:
            return 0;
    }
}

#endif
