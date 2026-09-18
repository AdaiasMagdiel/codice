#ifndef CODICE_BUILTINS_STAMPA_H
#define CODICE_BUILTINS_STAMPA_H

#include <stdint.h>

#include "../types.h"
#include "../vm.h"

Value builtin_stampa(uint32_t arg_count, VM *vm);

#endif
