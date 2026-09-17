#include <assert.h>
#include <stdio.h>
#include <stdlib.h>

#include "stampa.h"

// Same convention as snprintf; buf = NULL just measures the length.
static int format_value(char *buf, size_t bufsize, Value v)
{
    if (v.type == TYPE_INT)
    {
        return snprintf(buf, bufsize, "%d", v.as.as_int);
    }
    else if (v.type == TYPE_STRING)
    {
        return snprintf(buf, bufsize, "%s", v.as.as_string);
    }
    else if (v.type == TYPE_NULL)
    {
        return snprintf(buf, bufsize, "nullo");
    }
    else
    {
        fprintf(stderr, "Error: unimplemented type '%d' in format_value\n", v.type);
        assert(0);
        abort();
    }
}

Value builtin_stampa(uint32_t arg_count, VM *vm)
{
    Value *args = malloc(sizeof(Value) * arg_count);

    for (uint32_t i = 0; i < arg_count; i++)
    {
        args[i] = popVM(vm);
    }

    size_t length = 0;
    for (uint32_t i = 0; i < arg_count; i++)
    {
        length += (size_t)format_value(NULL, 0, args[i]);

        if (i < arg_count - 1)
            length += 1; // space
    }
    length += 1; // '\n'

    char *output = malloc(length + 1);
    size_t offset = 0;

    for (uint32_t i = 0; i < arg_count; i++)
    {
        offset += (size_t)format_value(output + offset, length + 1 - offset, args[i]);

        if (i < arg_count - 1)
            output[offset++] = ' ';
    }
    output[offset++] = '\n';
    output[offset] = '\0';

    fputs(output, stdout);

    free(output);
    free(args);

    Value null_val = {.type = TYPE_NULL, .as.as_int = 0};
    return null_val;
}
