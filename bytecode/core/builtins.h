#ifndef CODICE_BUILTINS_H
#define CODICE_BUILTINS_H

#include <stdint.h>

#include "types.h"

typedef Value (*BuiltinFn)(uint32_t arg_count, VM *vm);

// Returns NULL if no builtin with that name exists.
BuiltinFn find_builtin(const char *name);

#endif
