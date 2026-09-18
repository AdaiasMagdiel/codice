#include <string.h>

#include "builtins.h"
#include "builtins/stampa.h"

typedef struct
{
    const char *name;
    BuiltinFn fn;
} BuiltinEntry;

static const BuiltinEntry BUILTINS[] = {
    {"stampa", builtin_stampa},
};

static const size_t BUILTINS_COUNT = sizeof(BUILTINS) / sizeof(BUILTINS[0]);

BuiltinFn find_builtin(const char *name)
{
    for (size_t i = 0; i < BUILTINS_COUNT; i++)
    {
        if (strcmp(BUILTINS[i].name, name) == 0)
        {
            return BUILTINS[i].fn;
        }
    }

    return NULL;
}
