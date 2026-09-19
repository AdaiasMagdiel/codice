#include <stdlib.h>
#include <string.h>

#include "add.h"
#include "../vm.h"

int op_add(VM *vm)
{
    Value b = popVM(vm);
    Value a = popVM(vm);

    Value result;

    if (a.type == TYPE_INT && b.type == TYPE_INT)
    {
        result.type = TYPE_INT;
        result.as.as_int = a.as.as_int + b.as.as_int;
    }
    else if (a.type == TYPE_STR && b.type == TYPE_STR)
    {
        size_t len_a = strlen(a.as.as_string);
        size_t len_b = strlen(b.as.as_string);

        result.type = TYPE_STR;
        result.as.as_string = malloc(len_a + len_b + 1);

        if (result.as.as_string == NULL)
        {
            return -1;
        }

        memcpy(result.as.as_string, a.as.as_string, len_a);
        memcpy(result.as.as_string + len_a, b.as.as_string, len_b);
        result.as.as_string[len_a + len_b] = '\0';
    }
    else
    {
        return -2;
    }

    pushVM(vm, result);
    return 1;
}
