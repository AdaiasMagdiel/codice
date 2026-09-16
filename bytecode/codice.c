#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>

typedef enum {
	INT 	= 0x01,
	STRING  = 0x02
} ValueType;


typedef struct {
	ValueType type;
	union {
		int32_t as_int;
		char* string;
	} as;
} Value;

typedef struct {
	Value* stack;
	uint32_t capacity;
	Value* top;
} VM;

void push(VM *vm, Value value) {
	int current_size = vm->top - vm->stack;
    if (current_size >= vm->capacity) {
        vm->capacity *= 2;
        vm->stack = realloc(vm->stack, sizeof(Value) * vm->capacity);
        vm->top = vm->stack + current_size;
    }
    *vm->top = value;
    vm->top++;
}

void initVM(VM *vm, uint32_t initial_capacity) {
    vm->capacity = initial_capacity;
    vm->stack = (Value*) malloc(sizeof(Value) * vm->capacity);
    
    if (vm->stack == NULL) {
        fprintf(stderr, "Erro ao alocar memória para a pilha!\n");
        exit(1);
    }
    
    vm->top = vm->stack;
}

void freeVM(VM *vm) {
    free(vm->stack);
    vm->stack = NULL;
    vm->top = NULL;
    vm->capacity = 0;
}

int main() {
	int fileSize = 0;
	int $pos = 0;

	initVM(&vm, 256);

	FILE *fp = fopen("./output/program.codb", "r");
	if (fp == NULL) {
        printf("File Not Found!\n");
        return -1;
    }
    fseek(fp, 0, SEEK_END);
	fileSize = ftell(fp);
	fclose(fp);

	for ()

	freeVM(&vm);

	return 0;
}
